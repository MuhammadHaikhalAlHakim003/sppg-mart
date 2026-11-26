
import json
import re
import logging
from pathlib import Path

import fitz


# ==========================================
# 1. KONFIGURASI DIREKTORI
# ==========================================

BASE_DIR = Path(__file__).resolve().parent.parent

RAW_DIR = BASE_DIR / "knowledge_base" / "raw"
PROCESSED_DIR = BASE_DIR / "knowledge_base" / "processed"

OUTPUT_FILE = PROCESSED_DIR / "documents.jsonl"
REPORT_FILE = PROCESSED_DIR / "extraction_report.json"

# Konfigurasi chunking
CHUNK_SIZE = 1200
CHUNK_OVERLAP = 200

logging.basicConfig(
    level=logging.INFO,
    format="%(levelname)s: %(message)s"
)

logger = logging.getLogger(__name__)


# ==========================================
# 2. MEMBERSIHKAN TEKS
# ==========================================

def clean_text(text: str) -> str:
    """
    Membersihkan teks hasil ekstraksi PDF.
    """

    if not text:
        return ""

    # Menghapus karakter null
    text = text.replace("\x00", " ")

    # Menyeragamkan line ending
    text = text.replace("\r\n", "\n")
    text = text.replace("\r", "\n")

    # Menghapus spasi berlebihan
    text = re.sub(r"[ \t]+", " ", text)

    # Mengurangi baris kosong berlebihan
    text = re.sub(r"\n{3,}", "\n\n", text)

    # Menghapus spasi di awal dan akhir
    text = text.strip()

    return text


# ==========================================
# 3. MEMBACA PDF PER HALAMAN
# ==========================================

def extract_pdf(pdf_path: Path) -> list:
    """
    Mengekstrak teks dari setiap halaman PDF.
    """

    pages = []

    try:
        with fitz.open(pdf_path) as document:

            for page_number, page in enumerate(document, start=1):

                text = page.get_text("text")
                text = clean_text(text)

                if not text:
                    logger.warning(
                        "Halaman kosong: %s halaman %s",
                        pdf_path.name,
                        page_number
                    )
                    continue

                pages.append({
                    "page": page_number,
                    "text": text
                })

    except Exception as error:
        logger.error(
            "Gagal membaca %s: %s",
            pdf_path.name,
            error
        )

    return pages


# ==========================================
# 4. MEMECAH TEKS MENJADI CHUNK
# ==========================================

def split_text(text: str) -> list:
    """
    Memecah teks berdasarkan paragraf.
    Setiap chunk memiliki overlap agar konteks
    antarbagian tidak terputus terlalu jauh.
    """

    paragraphs = [
        paragraph.strip()
        for paragraph in text.split("\n\n")
        if paragraph.strip()
    ]

    chunks = []
    current_chunk = ""

    for paragraph in paragraphs:

        # Memecah paragraf sangat panjang
        if len(paragraph) > CHUNK_SIZE:

            if current_chunk:
                chunks.append(current_chunk)
                current_chunk = ""

            start = 0

            while start < len(paragraph):

                end = start + CHUNK_SIZE
                piece = paragraph[start:end].strip()

                if piece:
                    chunks.append(piece)

                start += CHUNK_SIZE - CHUNK_OVERLAP

            continue

        candidate = (
            current_chunk + "\n\n" + paragraph
            if current_chunk
            else paragraph
        )

        if len(candidate) <= CHUNK_SIZE:
            current_chunk = candidate

        else:
            if current_chunk:
                chunks.append(current_chunk)

            # Mengambil bagian akhir chunk sebelumnya
            overlap = current_chunk[-CHUNK_OVERLAP:]

            current_chunk = (
                overlap + "\n\n" + paragraph
                if overlap
                else paragraph
            )

    if current_chunk.strip():
        chunks.append(current_chunk.strip())

    return chunks


# ==========================================
# 5. MEMPROSES SELURUH DOKUMEN
# ==========================================

def process_documents():

    RAW_DIR.mkdir(parents=True, exist_ok=True)
    PROCESSED_DIR.mkdir(parents=True, exist_ok=True)

    pdf_files = sorted(RAW_DIR.glob("*.pdf"))

    if not pdf_files:
        logger.error("Tidak ditemukan file PDF di folder raw.")
        return

    all_chunks = []
    report = []

    logger.info("Ditemukan %s dokumen PDF.", len(pdf_files))

    for pdf_path in pdf_files:

        logger.info("Memproses: %s", pdf_path.name)

        pages = extract_pdf(pdf_path)

        document_chunk_count = 0

        for page_data in pages:

            chunks = split_text(page_data["text"])

            for chunk in chunks:

                if not chunk.strip():
                    continue

                record = {
                    "id": f"{pdf_path.stem}_p{page_data['page']}_c{document_chunk_count + 1}",
                    "source": pdf_path.name,
                    "page": page_data["page"],
                    "chunk_index": document_chunk_count + 1,
                    "text": chunk,
                    "char_count": len(chunk)
                }

                all_chunks.append(record)
                document_chunk_count += 1

        report.append({
            "filename": pdf_path.name,
            "pages_extracted": len(pages),
            "chunks_generated": document_chunk_count,
            "status": "success" if pages else "empty_or_failed"
        })

        logger.info(
            "Selesai: %s | %s halaman | %s chunks",
            pdf_path.name,
            len(pages),
            document_chunk_count
        )

    # ======================================
    # 6. MENYIMPAN HASIL JSONL
    # ======================================

    with OUTPUT_FILE.open("w", encoding="utf-8") as file:

        for chunk in all_chunks:
            file.write(
                json.dumps(chunk, ensure_ascii=False) + "\n"
            )

    # ======================================
    # 7. MENYIMPAN LAPORAN
    # ======================================

    report_data = {
        "total_documents": len(pdf_files),
        "total_chunks": len(all_chunks),
        "documents": report
    }

    with REPORT_FILE.open("w", encoding="utf-8") as file:

        json.dump(
            report_data,
            file,
            ensure_ascii=False,
            indent=4
        )

    print("\n====================================")
    print("PREPROCESSING SELESAI")
    print("====================================")
    print(f"Total PDF       : {len(pdf_files)}")
    print(f"Total chunks    : {len(all_chunks)}")
    print(f"Output JSONL    : {OUTPUT_FILE}")
    print(f"Laporan         : {REPORT_FILE}")
    print("====================================")


# ==========================================
# 8. EKSEKUSI PROGRAM
# ==========================================

if __name__ == "__main__":
    process_documents()
