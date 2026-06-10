import sys
import os

# Mematikan pengecekan koneksi model hoster agar proses scan instan
os.environ["PADDLE_PDX_DISABLE_MODEL_SOURCE_CHECK"] = "True"

# Mengamankan flag oneDNN untuk Windows CPU environment
os.environ["FLAGS_use_onednn"] = "0"
os.environ["FLAGS_use_mkldnn"] = "0"

# Memaksa sys.stdout menggunakan encoding UTF-8 agar string aman dibaca PHP
sys.stdout.reconfigure(encoding="utf-8")

try:
    # --------------------------------------------------------------------------
    # FORCED OVERRIDE DEFAULT CONFIG PADDLEX
    # Kita intercept konfigurasi default pipelines milik PaddleX langsung ke model mobile
    # --------------------------------------------------------------------------
    import paddlex

    if hasattr(paddlex, "inference") and hasattr(paddlex.inference, "pipelines"):
        from paddlex.inference.pipelines.ocr import OCRPipeline

        # Setel ulang properti class default secara global sebelum class diinstansiasi
        # Ini akan memaksa sistem mendownload dan memakai versi mobile yang super ringan (1-2 detik)
        OCRPipeline._default_det_model = "PP-OCRv5_mobile_det"
        OCRPipeline._default_rec_model = "en_PP-OCRv5_mobile_rec"

    from paddleocr import PaddleOCR
except Exception as e:
    print(f"ERROR: Framework initialization failed: {str(e)}")
    sys.exit(1)

if len(sys.argv) < 2:
    print("ERROR: Please provide image path")
    sys.exit(1)

image_path = (
    sys.argv[2 if sys.argv[1] == "--" else 1] if len(sys.argv) > 2 else sys.argv[1]
)

if not os.path.exists(image_path):
    print(f"ERROR: Image not found at {image_path}")
    sys.exit(1)

try:
    # Inisialisasi polos tanpa argumen ilegal agar tidak memicu 'Unknown argument'
    ocr = PaddleOCR(
        use_doc_orientation_classify=False,
        use_doc_unwarping=False,
        use_textline_orientation=False,
        lang="en",
        engine="transformers",  # Kebal dari crash oneDNN C++ di Windows
    )

    # Jalankan prediksi pipeline murni
    result = ocr.predict(image_path)

    texts = []
    if result:
        if not isinstance(result, list):
            try:
                result = list(result)
            except Exception:
                pass

        for res in result:
            # Format Dictionary (Standard PaddleX / Transformers)
            if isinstance(res, dict):
                if "rec_texts" in res and isinstance(res["rec_texts"], list):
                    texts.extend(res["rec_texts"])
                elif "texts" in res and isinstance(res["texts"], list):
                    texts.extend(res["texts"])

            # Format List Tradisional [[box, (text, score)]]
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

    # Pembersihan whitespace akhir
    texts = [t.strip() for t in texts if t and str(t).strip()]

    if texts:
        print("\n".join(texts))
    else:
        print(f"ERROR: Teks kosong. Raw output: {str(result)}")

except Exception as e:
    print(f"ERROR: OCR failed: {str(e)}")
    sys.exit(1)
