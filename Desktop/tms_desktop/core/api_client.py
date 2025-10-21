import requests
from config import API_BASE_URL, API_TOKEN

headers = {"Authorization": f"Bearer {API_TOKEN}"}

def get(endpoint, params=None):
    url = f"{API_BASE_URL}/{endpoint}"
    response = requests.get(url, headers=headers, params=params)
    response.raise_for_status()
    return response.json()

def post(endpoint, data=None, files=None):
    url = f"{API_BASE_URL}/{endpoint}"
    response = requests.post(url, headers=headers, json=data, files=files)
    response.raise_for_status()
    return response.json()
