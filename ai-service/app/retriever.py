
import json
import logging
from pathlib import Path

import faiss
from sentence_transformers import SentenceTransformer


# ==========================================
# 1. KONFIGURASI
# ==========================================

BASE_DIR = Path(__file__).resolve().parent.parent

VECTOR_DIR = BASE_DIR / "knowledge_base" / "vector_store"

INDEX_FILE = VECTOR_DIR / "index.faiss"
METADATA_FILE = VECTOR_DIR / "metadata.json"

MODEL_NAME = "intfloat/multilingual-e5-small"

DEFAULT_TOP_K = 5

logger = logging.getLogger(__name__)


# ==========================================
# 2. CLASS RETRIEVER
# ==========================================

class KnowledgeBaseRetriever:

    def __init__(self):

        if not INDEX_FILE.exists():
            raise FileNotFoundError(
                f"FAISS index tidak ditemukan: {INDEX_FILE}"
            )

        if not METADATA_FILE.exists():
            raise FileNotFoundError(
                f"Metadata tidak ditemukan: {METADATA_FILE}"
            )

        logger.info("Memuat FAISS index...")

        self.index = faiss.read_index(str(INDEX_FILE))

        logger.info("Memuat metadata...")

        with METADATA_FILE.open(
            "r",
            encoding="utf-8"
        ) as file:
            metadata = json.load(file)

        self.documents = metadata["documents"]

        logger.info("Memuat embedding model...")

        self.model = SentenceTransformer(MODEL_NAME)

        if self.index.ntotal != len(self.documents):
            raise ValueError(
                "Jumlah vector tidak sama dengan jumlah metadata."
            )

        logger.info(
            "Retriever siap. Total vector: %s",
            self.index.ntotal
        )

    # ======================================
    # 3. PENCARIAN DOKUMEN
    # ======================================

    def search(self, question: str, top_k: int = DEFAULT_TOP_K):

        question = question.strip()

        if not question:
            return []

        if top_k < 1:
            return []

        # Format query khusus multilingual-e5
        query_text = "query: " + question

        query_embedding = self.model.encode(
            [query_text],
            convert_to_numpy=True,
            normalize_embeddings=True
        )

        query_embedding = query_embedding.astype("float32")

        # Membatasi jumlah hasil sesuai jumlah vector
        top_k = min(top_k, self.index.ntotal)

        scores, indices = self.index.search(
            query_embedding,
            top_k
        )

        results = []

        for score, index_position in zip(
            scores[0],
            indices[0]
        ):

            if index_position < 0:
                continue

            document = self.documents[int(index_position)]

            results.append({
                "id": document.get("id"),
                "source": document.get("source"),
                "page": document.get("page"),
                "text": document.get("text"),
                "similarity": round(float(score), 4)
            })

        return results
