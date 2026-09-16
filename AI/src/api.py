from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List
from src.predict import predict_disease


app = FastAPI(
    title="Vitalis AI Service",
    description="API de prédiction des maladies à partir des symptômes.",
    version="1.0.0"
)


class PredictionRequest(BaseModel):
    symptoms: List[str]


@app.get("/")
def root():
    return {
        "service": "Vitalis AI Service",
        "status": "running"
    }


@app.get("/health")
def health():
    return {
        "status": "ok",
        "model": "loaded"
    }


@app.post("/predict")
def predict(request: PredictionRequest):
    try:
        disease, confidence = predict_disease(request.symptoms)

        return {
            "disease": disease,
            "confidence": round(float(confidence), 2)
        }

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=str(e)
        )