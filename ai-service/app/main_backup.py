
# import os
# import logging
# import secrets

# from pathlib import Path
# from typing import Literal

# from dotenv import load_dotenv
# from fastapi import FastAPI, Header, HTTPException
# from pydantic import BaseModel, Field

# from openai import (
#     OpenAI,
#     AuthenticationError,
#     RateLimitError,
#     APIConnectionError,
#     APIStatusError,
# )


# # =====================================================
# # 1. CONFIGURATION
# # =====================================================

# BASE_DIR = Path(__file__).resolve().parent.parent
# ENV_PATH = BASE_DIR / ".env"

# load_dotenv(ENV_PATH, override=True)

# OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
# OPENAI_MODEL = os.getenv("OPENAI_MODEL", "gpt-6-luna")
# INTERNAL_API_TOKEN = os.getenv("INTERNAL_API_TOKEN")

# logging.basicConfig(level=logging.INFO)
# logger = logging.getLogger(__name__)


# if not OPENAI_API_KEY:
#     raise RuntimeError("OPENAI_API_KEY tidak ditemukan dalam file .env")

# if not INTERNAL_API_TOKEN:
#     raise RuntimeError("INTERNAL_API_TOKEN tidak ditemukan dalam file .env")


# # =====================================================
# # 2. OPENAI CLIENT
# # =====================================================

# client = OpenAI(
#     api_key=OPENAI_API_KEY,
#     timeout=60.0,
#     max_retries=2
# )


# # =====================================================
# # 3. FASTAPI APPLICATION
# # =====================================================

# app = FastAPI(
#     title="SPPG AI Assistant API",
#     description=(
#         "Backend Asisten Informasi Berbasis Large Language Model "
#         "untuk SPPG Desa Temu"
#     ),
#     version="1.0.0"
# )


# # =====================================================
# # 4. SYSTEM INSTRUCTION
# # =====================================================

# SYSTEM_PROMPT = """
# Kamu adalah Asisten Informasi SPPG yang menggunakan bahasa Indonesia.

# Tugas utama:
# 1. Memberikan informasi umum mengenai SPPG.
# 2. Menjelaskan program Makan Bergizi Gratis (MBG).
# 3. Membantu pengguna memahami fungsi dan kegiatan SPPG.
# 4. Menjawab pertanyaan dengan bahasa sederhana, jelas, dan sopan.
# 5. Memberikan jawaban yang terstruktur dan mudah dipahami.

# ATURAN PENTING:

# - Gunakan bahasa Indonesia.
# - Jawab pertanyaan secara langsung dan relevan.
# - Hindari jawaban yang terlalu panjang jika tidak diperlukan.
# - Jangan mengarang informasi, data, jadwal, atau prosedur resmi.
# - Jangan mengklaim mengetahui data internal SPPG Desa Temu
#   apabila informasi tersebut belum diberikan.
# - Jika pertanyaan membutuhkan data internal yang belum tersedia,
#   sampaikan bahwa informasi tersebut belum tersedia.
# - Bedakan informasi umum dengan informasi resmi dari pihak SPPG.
# - Jika informasi belum dapat dipastikan, jelaskan keterbatasannya.

# Kamu bertugas sebagai asisten informasi, bukan sebagai pihak
# yang berwenang menetapkan kebijakan atau prosedur resmi SPPG.
# """


# # =====================================================
# # 5. REQUEST SCHEMA
# # =====================================================

# class ChatHistoryMessage(BaseModel):
#     role: Literal["user", "assistant"]
#     content: str = Field(min_length=1, max_length=3000)


# class ChatRequest(BaseModel):
#     question: str = Field(min_length=1, max_length=4000)
#     history: list[ChatHistoryMessage] = Field(default_factory=list)


# class ChatResponse(BaseModel):
#     answer: str
#     model: str
#     status: str


# # =====================================================
# # 6. INTERNAL API AUTHENTICATION
# # =====================================================

# def verify_internal_token(token: str | None):

#     if not token:
#         raise HTTPException(
#             status_code=401,
#             detail="Internal API token tidak ditemukan."
#         )

#     if not secrets.compare_digest(token, INTERNAL_API_TOKEN):
#         raise HTTPException(
#             status_code=401,
#             detail="Internal API token tidak valid."
#         )


# # =====================================================
# # 7. ROOT ENDPOINT
# # =====================================================

# @app.get("/")
# def root():

#     return {
#         "application": "SPPG AI Assistant API",
#         "version": "1.0.0",
#         "provider": "OpenAI",
#         "model": OPENAI_MODEL,
#         "status": "running"
#     }


# # =====================================================
# # 8. HEALTH CHECK
# # =====================================================

# @app.get("/health")
# def health():

#     return {
#         "status": "healthy",
#         "provider": "OpenAI",
#         "model": OPENAI_MODEL
#     }


# # =====================================================
# # 9. CHAT ENDPOINT
# # =====================================================

# @app.post("/chat", response_model=ChatResponse)
# def chat(
#     request: ChatRequest,
#     x_internal_token: str | None = Header(
#         default=None,
#         alias="X-Internal-Token"
#     )
# ):

#     # Verify internal token
#     verify_internal_token(x_internal_token)

#     question = request.question.strip()

#     if not question:
#         raise HTTPException(
#             status_code=422,
#             detail="Pertanyaan tidak boleh kosong."
#         )

#     if len(request.history) > 20:
#         raise HTTPException(
#             status_code=422,
#             detail="Riwayat percakapan maksimal 20 pesan."
#         )

#     # Prepare conversation input
#     conversation_input = []

#     for message in request.history:

#         conversation_input.append({
#             "role": message.role,
#             "content": message.content
#         })

#     conversation_input.append({
#         "role": "user",
#         "content": question
#     })

#     try:

#         # Send request to OpenAI Responses API
#         response = client.responses.create(
#             model=OPENAI_MODEL,
#             instructions=SYSTEM_PROMPT,
#             input=conversation_input,
#             max_output_tokens=350,
#             store=False
#         )

#         answer = response.output_text

#         if not answer:
#             raise HTTPException(
#                 status_code=502,
#                 detail="Model tidak menghasilkan jawaban."
#             )

#         logger.info("Chat request berhasil diproses.")

#         return ChatResponse(
#             answer=answer,
#             model=OPENAI_MODEL,
#             status="success"
#         )

#     except AuthenticationError:

#         logger.exception("OpenAI authentication gagal.")

#         raise HTTPException(
#             status_code=502,
#             detail="Autentikasi OpenAI API gagal. Periksa API Key."
#         )

#     except RateLimitError:

#         logger.exception("OpenAI rate limit tercapai.")

#         raise HTTPException(
#             status_code=429,
#             detail="Batas penggunaan API tercapai. Coba kembali nanti."
#         )

#     except APIConnectionError:

#         logger.exception("Koneksi ke OpenAI gagal.")

#         raise HTTPException(
#             status_code=503,
#             detail="Tidak dapat terhubung ke layanan OpenAI."
#         )

#     except APIStatusError:

#         logger.exception("OpenAI API mengembalikan kesalahan.")

#         raise HTTPException(
#             status_code=502,
#             detail="OpenAI API mengalami kesalahan saat memproses permintaan."
#         )

#     except HTTPException:
#         raise

#     except Exception:

#         logger.exception("Kesalahan internal pada endpoint chat.")

#         raise HTTPException(
#             status_code=500,
#             detail="Terjadi kesalahan internal pada server."
#         )
