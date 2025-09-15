from fastapi import FastAPI
from pydantic import BaseModel
import openai
import os
import json

# Imposta la tua API Key di OpenAI come variabile d'ambiente
openai.api_key = ('sk-7162a3d3b8a743678681dd06cc819879')

app = FastAPI()

# Modello della richiesta
class TestRequest(BaseModel):
    materia: str
    argomento: str
    tipo_test: str = "scelta multipla"
    numero_domande: int = 5
    livello_difficolta: str = "medio"  # aggiunto parametro opzionale

@app.post("/genera_test_avanzato")
async def genera_test_avanzato(req: TestRequest):
    prompt = f"""
    Genera {req.numero_domande} domande di tipo '{req.tipo_test}' su '{req.argomento}' 
    per la materia '{req.materia}', livello di difficoltà '{req.livello_difficolta}'.
    Per ogni domanda crea:
    - 'domanda': testo della domanda
    - 'opzioni': lista di risposte possibili (solo se scelta multipla o vero/falso)
    - 'risposta_corretta': la risposta corretta
    Restituisci tutto in JSON valido, con chiave principale 'test' contenente tutte le domande.
    """

    try:
        response = openai.ChatCompletion.create(
            model="gpt-5-mini",
            messages=[{"role": "user", "content": prompt}],
            temperature=0.7
        )

        content = response.choices[0].message['content']

        # Provo a convertire in JSON
        test_json = json.loads(content)
        return {"success": True, "test": test_json}

    except json.JSONDecodeError:
        return {"success": False, "error": "Impossibile convertire la risposta in JSON", "raw": content}

    except Exception as e:
        return {"success": False, "error": str(e)}
