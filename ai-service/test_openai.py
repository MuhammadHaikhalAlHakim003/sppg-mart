
import os
from pathlib import Path

from dotenv import load_dotenv
from openai import (
    OpenAI,
    AuthenticationError,
    RateLimitError,
    APIConnectionError,
    APIStatusError
)


# =====================================================
# 1. LOAD ENVIRONMENT
# =====================================================

BASE_DIR = Path(__file__).resolve().parent
ENV_PATH = BASE_DIR / ".env"

load_dotenv(ENV_PATH, override=True)

API_KEY = os.getenv("OPENAI_API_KEY")
MODEL = os.getenv("OPENAI_MODEL", "gpt-6-luna")


# =====================================================
# 2. VALIDATE CONFIGURATION
# =====================================================

print("=" * 50)
print("SPPG AI ASSISTANT - OPENAI CONNECTION TEST")
print("=" * 50)

if not API_KEY:
    print("STATUS: GAGAL")
    print("OPENAI_API_KEY tidak ditemukan.")
    raise SystemExit(1)

print("Provider :", "OpenAI")
print("Model    :", MODEL)
print("API Key  :", "Terdeteksi")


# =====================================================
# 3. INITIALIZE CLIENT
# =====================================================

client = OpenAI(
    api_key=API_KEY,
    timeout=60.0,
    max_retries=2
)


# =====================================================
# 4. TEST MODEL GENERATION
# =====================================================

try:

    print("\nMenghubungkan ke OpenAI API...")

    response = client.responses.create(
        model=MODEL,
        instructions=(
            "Kamu adalah Asisten Informasi SPPG. "
            "Jawab menggunakan bahasa Indonesia yang sederhana. "
            "Jawab secara singkat dan jelas."
        ),
        input=(
            "Halo, perkenalkan diri Anda sebagai "
            "Asisten Informasi SPPG."
        ),
        max_output_tokens=200,
        store=False
    )

    print("\n" + "=" * 50)
    print("STATUS: BERHASIL")
    print("=" * 50)

    print("\nJawaban GPT:")
    print(response.output_text)

    print("\nModel:", MODEL)
    print("OPENAI API CONNECTION SUCCESS")

except AuthenticationError:
    print("\nSTATUS: GAGAL")
    print("API Key tidak valid atau tidak memiliki akses.")

except RateLimitError:
    print("\nSTATUS: GAGAL")
    print("Rate limit atau kuota API tercapai.")

except APIConnectionError:
    print("\nSTATUS: GAGAL")
    print("Tidak dapat terhubung ke OpenAI API.")

except APIStatusError as error:
    print("\nSTATUS: GAGAL")
    print("HTTP Status:", error.status_code)
    print("Detail:", str(error))

except Exception as error:
    print("\nSTATUS: GAGAL")
    print("Tipe Error:", type(error).__name__)
    print("Detail:", str(error))

print("\n" + "=" * 50)
print("END OF TEST")
print("=" * 50)
