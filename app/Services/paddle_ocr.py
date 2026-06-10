import os
import sys
import socket
import json
import threading

os.environ["PADDLE_PDX_DISABLE_MODEL_SOURCE_CHECK"] = "True"
os.environ["FLAGS_use_onednn"] = "0"
os.environ["FLAGS_use_mkldnn"] = "0"

sys.stdout.reconfigure(encoding="utf-8")

print("[Daemon] Loading PaddleOCR model...", flush=True)

try:
    import paddlex

    if hasattr(paddlex, "inference") and hasattr(paddlex.inference, "pipelines"):
        from paddlex.inference.pipelines.ocr import OCRPipeline

        OCRPipeline._default_det_model = "PP-OCRv5_mobile_det"
        OCRPipeline._default_rec_model = "en_PP-OCRv5_mobile_rec"

    from paddleocr import PaddleOCR

    ocr = PaddleOCR(
        use_doc_orientation_classify=False,
        use_doc_unwarping=False,
        use_textline_orientation=False,
        lang="en",
    )
    print("[Daemon] Model loaded. Ready.", flush=True)
except Exception as e:
    print(f"[Daemon] FATAL: {e}", flush=True)
    sys.exit(1)


def handle_client(conn):
    try:
        data = b""
        while True:
            chunk = conn.recv(4096)
            if not chunk:
                break
            data += chunk
            if data.endswith(b"\n"):
                break

        image_path = data.decode("utf-8").strip()

        if not os.path.exists(image_path):
            conn.sendall(
                json.dumps({"error": f"File not found: {image_path}"}).encode() + b"\n"
            )
            return

        result = ocr.predict(image_path)
        texts = []

        if result:
            if not isinstance(result, list):
                result = list(result)
            for res in result:
                if isinstance(res, dict):
                    if "rec_texts" in res:
                        texts.extend(res["rec_texts"])
                    elif "texts" in res:
                        texts.extend(res["texts"])
                elif isinstance(res, list):
                    for line in res:
                        if (
                            isinstance(line, list)
                            and len(line) > 1
                            and isinstance(line[1], tuple)
                        ):
                            texts.append(line[1][0])
                        elif isinstance(line, str):
                            texts.append(line)

        texts = [t.strip() for t in texts if t and str(t).strip()]
        output = "\n".join(texts) if texts else "ERROR: Teks kosong"
        conn.sendall(json.dumps({"text": output}).encode() + b"\n")

    except Exception as e:
        conn.sendall(json.dumps({"error": str(e)}).encode() + b"\n")
    finally:
        conn.close()


SOCKET_PATH = "/tmp/paddleocr_daemon.sock"

if os.path.exists(SOCKET_PATH):
    os.remove(SOCKET_PATH)

server = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
server.bind(SOCKET_PATH)
os.chmod(SOCKET_PATH, 0o777)
server.listen(5)

print(f"[Daemon] Listening on {SOCKET_PATH}", flush=True)

while True:
    conn, _ = server.accept()
    threading.Thread(target=handle_client, args=(conn,), daemon=True).start()
