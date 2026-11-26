
import json
import logging
from pathlib import Path

import faiss
import numpy as np
from sentence_transformers import SentenceTransformer


# ==========================================
# 1. KONFIGURASI
# ==========================================

BASE_DIR = Path(__file__).resolve().parent.parent

INPUT_FILE = (
    BASE_DIR
    / "knowledge_base"
    / "processed"
    / "documents.jsonl"
)

VECTOR_DIR = (
    BASE_DIR
    / "knowledge_base"
    / "vector_store"
)

INDEX_FILE = VECTOR_DIR / "index.faiss"
METADATA_FILE = VECTOR_DIR / "metadata.json"

MODEL_NAME = "intfloat/multilingual-e5-small"

BATCH_SIZE = 16

logging.basicConfig(
    level=logging.INFO,
    format="%(levelname)s: %(message)s"
)

logger = logging.getLogger(__name__)


# ==========================================
# 2. MEMBACA DATA CHUNKS
# ==========================================

def load_documents():

    if not INPUT_FILE.exists():
        raise FileNotFoundError(
            f"File tidak ditemukan: {INPUT_FILE}"
        )

    documents = []

    with INPUT_FILE.open("r", encoding="utf-8") as file:

        for line_number, line in enumerate(file, start=1):

            if not line.strip():
                continue

            try:
                record = json.loads(line)

            except json.JSONDecodeError as error:
                logger.warning(
                    "JSON tidak valid pada baris %s: %s",
                    line_number,
                    error
                )
                continue

            text = record.get("text", "").strip()

            if not text:
                continue

            documents.append(record)

    if not documents:
        raise ValueError(
            "Tidak ada dokumen valid untuk dibuat embedding."
        )

    return documents


# ==========================================
# 3. MEMBANGUN EMBEDDING
# ==========================================

def build_embeddings(documents):

    logger.info("Memuat model embedding: %s", MODEL_NAME)

    model = SentenceTransformer(MODEL_NAME)

    texts = [
        "passage: " + document["text"]
        for document in documents
    ]

    logger.info(
        "Membuat embedding untuk %s chunks...",
        len(texts)
    )

    embeddings = model.encode(
        texts,
        batch_size=BATCH_SIZE,
        show_progress_bar=True,
        convert_to_numpy=True,
        normalize_embeddings=True
    )

    embeddings = np.asarray(
        embeddings,
        dtype=np.float32
    )

    return embeddings


# ==========================================
# 4. MEMBANGUN FAISS INDEX
# ==========================================

def build_faiss_index(embeddings):

    dimension = embeddings.shape[1]

    index = faiss.IndexFlatIP(dimension)

    index.add(embeddings)

    return index


# ==========================================
# 5. MENYIMPAN INDEX DAN METADATA
# ==========================================

def save_vector_store(index, documents):

    VECTOR_DIR.mkdir(
        parents=True,
        exist_ok=True
    )

    faiss.write_index(
        index,
        str(INDEX_FILE)
    )

    metadata = {
        "model_name": MODEL_NAME,
        "total_vectors": len(documents),
        "embedding_dimension": index.d,
        "documents": documents
    }

    with METADATA_FILE.open(
        "w",
        encoding="utf-8"
    ) as file:

        json.dump(
            metadata,
            file,
            ensure_ascii=False,
            indent=2
        )

    logger.info("FAISS index berhasil disimpan.")
    logger.info("Metadata berhasil disimpan.")


# ==========================================
# 6. PROSES UTAMA
# ==========================================

def main():

    try:

        logger.info("Memulai pembangunan vector store...")

        documents = load_documents()

        logger.info(
            "Total dokumen chunks: %s",
            len(documents)
        )

        embeddings = build_embeddings(documents)

        index = build_faiss_index(embeddings)

        save_vector_store(
            index,
            documents
        )

        print("\n===================================")
        print("VECTOR STORE BERHASIL DIBANGUN")
        print("===================================")
        print(f"Total vectors : {index.ntotal}")
        print(f"Dimensi       : {index.d}")
        print(f"Model         : {MODEL_NAME}")
        print(f"Index         : {INDEX_FILE}")
        print(f"Metadata      : {METADATA_FILE}")
        print("===================================")

    except Exception as error:

        logger.exception(
            "Gagal membangun vector store: %s",
            error
        )

        raise


if __name__ == "__main__":
    main()
