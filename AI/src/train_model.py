import pandas as pd
import joblib
from pathlib import Path
from sklearn.model_selection import train_test_split

from sklearn.linear_model import LogisticRegression
from sklearn.tree import DecisionTreeClassifier
from sklearn.ensemble import RandomForestClassifier

from sklearn.metrics import (
    accuracy_score,
    classification_report,
    confusion_matrix
)

# ==========================================
# 1. Load dataset
# ==========================================

BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_PATH = BASE_DIR / "data" / "disease_dataset.csv"

df = pd.read_csv(DATASET_PATH)

print("Dataset loaded.")
print("Shape:", df.shape)


# ==========================================
# 2. Separate Features and Target
# ==========================================

X = df.drop(columns=["disease"])
y = df["disease"]

print("\nFeatures:")
print(X.columns.tolist())

print("\nTarget:")
print(y.name)


# ==========================================
# 3. Split dataset
# ==========================================

X_train, X_test, y_train, y_test = train_test_split(
    X,
    y,
    test_size=0.20,
    random_state=42,
    stratify=y
)

print("\nTraining samples:", len(X_train))
print("Testing samples:", len(X_test))


# ==========================================
# 4. Create Models
# ==========================================

models = {

    "Logistic Regression": LogisticRegression(
        max_iter=1000,
        random_state=42
    ),

    "Decision Tree": DecisionTreeClassifier(
        random_state=42
    ),

    "Random Forest": RandomForestClassifier(
        n_estimators=200,
        random_state=42,
        class_weight="balanced"
    )
}


# Store results
results = {}

# Store trained models
trained_models = {}


# ==========================================
# 5. Train and Evaluate Models
# ==========================================

for model_name, model in models.items():

    print("\n" + "=" * 60)
    print("MODEL:", model_name)
    print("=" * 60)

    # Train model
    print("\nTraining model...")

    model.fit(X_train, y_train)

    print("Training completed.")

    # Prediction
    y_pred = model.predict(X_test)

    # Accuracy
    accuracy = accuracy_score(
        y_test,
        y_pred
    )

    results[model_name] = accuracy
    trained_models[model_name] = model

    print(
        "\nAccuracy:",
        round(accuracy * 100, 2),
        "%"
    )

    # Classification report
    print("\nClassification Report:")

    print(
        classification_report(
            y_test,
            y_pred
        )
    )

    # Confusion matrix
    print("\nConfusion Matrix:")

    print(
        confusion_matrix(
            y_test,
            y_pred
        )
    )


# ==========================================
# 6. Compare Models
# ==========================================

print("\n" + "=" * 60)
print("MODEL COMPARISON")
print("=" * 60)

for model_name, accuracy in results.items():

    print(
        model_name,
        "->",
        round(accuracy * 100, 2),
        "%"
    )


# ==========================================
# 7. Select Best Model
# ==========================================

best_model_name = max(
    results,
    key=results.get
)

best_model = trained_models[
    best_model_name
]

best_accuracy = results[
    best_model_name
]

print("\n" + "=" * 60)
print("BEST MODEL")
print("=" * 60)

print(
    "Best Model:",
    best_model_name
)

print(
    "Best Accuracy:",
    round(
        best_accuracy * 100,
        2
    ),
    "%"
)


# ==========================================
# 8. Feature Importance
# ==========================================

# Feature importance is available for
# Decision Tree and Random Forest

if hasattr(
    best_model,
    "feature_importances_"
):

    importance = pd.DataFrame({
        "symptom": X.columns,
        "importance": best_model.feature_importances_
    })

    importance = importance.sort_values(
        by="importance",
        ascending=False
    )

    print("\n" + "=" * 60)
    print("FEATURE IMPORTANCE")
    print("=" * 60)

    print(importance)

else:

    print(
        "\nFeature importance is not available "
        "for the selected model."
    )


# ==========================================
# 9. Save Best Model
# ==========================================

MODELS_DIR = BASE_DIR / "models"
MODELS_DIR.mkdir(parents=True, exist_ok=True)

MODEL_PATH = MODELS_DIR / "disease_model.joblib"

joblib.dump(
    best_model,
    MODEL_PATH
)

print("\n" + "=" * 60)
print("MODEL SAVED")
print("=" * 60)

print(
    "Selected model:",
    best_model_name
)

print(
    "Model saved to:",
    MODEL_PATH
)