
import sys
from pathlib import Path

# Memastikan folder utama proyek dapat diakses
BASE_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(BASE_DIR))

from app.retriever import KnowledgeBaseRetriever


def main():

    print("\n====================================")
    print("TEST KNOWLEDGE BASE RETRIEVAL")
    print("====================================")

    retriever = KnowledgeBaseRetriever()

    question = input(
        "\nMasukkan pertanyaan tentang MBG/SPPG: "
    ).strip()

    results = retriever.search(
        question=question,
        top_k=5
    )

    print("\n====================================")
    print("HASIL PENCARIAN")
    print("====================================")

    if not results:
        print("Tidak ditemukan hasil pencarian.")
        return

    for number, result in enumerate(results, start=1):

        print(f"\nHASIL KE-{number}")
        print("------------------------------------")

        print(f"Sumber     : {result['source']}")
        print(f"Halaman    : {result['page']}")
        print(f"Similarity : {result['similarity']}")

        print("\nIsi dokumen:")
        print(result["text"])

    print("\n====================================")
    print("TEST RETRIEVAL SELESAI")
    print("====================================")


if __name__ == "__main__":
    main()
