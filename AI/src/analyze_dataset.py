from pathlib import Path
import pandas as pd

# Load dataset
BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_PATH = BASE_DIR / "data" / "disease_dataset.csv"

df = pd.read_csv(DATASET_PATH)


print("=" * 50)
print("DATASET INFORMATION")
print("=" * 50)

print("Number of records:", len(df))
print("Number of columns:", len(df.columns))

print("\nColumns:")
print(df.columns.tolist())


# --------------------------------------------------
# Disease distribution
# --------------------------------------------------

print("\n" + "=" * 50)
print("DISEASE DISTRIBUTION")
print("=" * 50)

print(df["disease"].value_counts())


# --------------------------------------------------
# Missing values
# --------------------------------------------------

print("\n" + "=" * 50)
print("MISSING VALUES")
print("=" * 50)

print(df.isnull().sum())


# --------------------------------------------------
# Duplicate rows
# --------------------------------------------------

print("\n" + "=" * 50)
print("DUPLICATES")
print("=" * 50)

print("Duplicate rows:", df.duplicated().sum())


# --------------------------------------------------
# Feature values
# --------------------------------------------------

print("\n" + "=" * 50)
print("FEATURE VALUES")
print("=" * 50)

features = df.drop(columns=["disease"])

for column in features.columns:
    print(
        column,
        "->",
        sorted(df[column].unique())
    )


# --------------------------------------------------
# Average symptoms by disease
# --------------------------------------------------

print("\n" + "=" * 50)
print("SYMPTOM DISTRIBUTION BY DISEASE")
print("=" * 50)

symptom_distribution = df.groupby("disease")[features.columns].mean()

print(symptom_distribution.round(2))