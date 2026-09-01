from pathlib import Path
import joblib
import pandas as pd

BASE_DIR = Path(__file__).resolve().parent.parent
MODEL_PATH = BASE_DIR / "models" / "disease_model.joblib"

model = joblib.load(MODEL_PATH)
print("Model loaded successfully.")


# ==========================================
# 2. Features used during training
# ==========================================

FEATURES = [
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


# ==========================================
# 3. Prediction function
# ==========================================

def predict_disease(symptoms):

    # Initialize all symptoms to 0
    patient_data = {
        feature: 0
        for feature in FEATURES
    }

    # Set selected symptoms to 1
    for symptom in symptoms:

        if symptom in patient_data:
            patient_data[symptom] = 1

    # Convert patient data to DataFrame
    patient_df = pd.DataFrame(
        [patient_data],
        columns=FEATURES
    )

    # Predict disease
    prediction = model.predict(patient_df)[0]

    # Prediction probabilities
    probabilities = model.predict_proba(patient_df)[0]

    # Get confidence
    confidence = max(probabilities) * 100

    return prediction, confidence


# ==========================================
# 4. Test prediction
# ==========================================

if __name__ == "__main__":

    test_symptoms = [
        "fever",
        "cough",
        "fatigue",
        "muscle_pain"
    ]

    disease, confidence = predict_disease(
        test_symptoms
    )

    print("\n================================")
    print("DISEASE PREDICTION")
    print("================================")

    print("Symptoms:")
    print(test_symptoms)

    print("\nPredicted disease:")
    print(disease)

    print(
        "\nConfidence:",
        round(confidence, 2),
        "%"
    )