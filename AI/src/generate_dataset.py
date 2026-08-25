import random
import pandas as pd
from disease_rules import DISEASES


SYMPTOMS = [
    "fever",
    "cough",
    "headache",
    "fatigue",
    "sore_throat",
    "runny_nose",
    "shortness_of_breath",
    "chest_pain",
    "nausea",
    "vomiting",
    "diarrhea",
    "abdominal_pain",
    "skin_rash",
    "itching",
    "joint_pain",
    "muscle_pain",
    "dizziness"
]


def generate_patient(disease_symptoms):

    patient = {}

    for symptom in SYMPTOMS:

        probability = disease_symptoms.get(symptom, 0.05)

        patient[symptom] = (
            1 if random.random() < probability else 0
        )

    return patient


def generate_dataset(samples_per_disease=1000):

    data = []

    for disease, symptoms in DISEASES.items():

        for _ in range(samples_per_disease):

            patient = generate_patient(symptoms)

            patient["disease"] = disease

            data.append(patient)

    return pd.DataFrame(data)

if __name__ == "__main__":

    from pathlib import Path

    df = generate_dataset(1000)

    # Shuffle the dataset
    df = df.sample(
        frac=1,
        random_state=42
    ).reset_index(drop=True)

    base_dir = Path(__file__).resolve().parent.parent
    data_dir = base_dir / "data"

    data_dir.mkdir(parents=True, exist_ok=True)

    output_path = data_dir / "disease_dataset.csv"

    df.to_csv(
        output_path,
        index=False
    )

    print("================================")
    print("Dataset generated successfully!")
    print("================================")

    print("Records:", len(df))
    print("Features:", len(df.columns) - 1)

    print("\nDiseases:")
    print(df["disease"].value_counts())

    print("\nFirst records:")
    print(df.head())

    print("\nDataset saved to:")
    print(output_path)