// Base URL de l'API Laravel
export const apiUrl =
  import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

// GET
export async function apiGet(path) {
  const response = await fetch(`${apiUrl}${path}`, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    },
  })

  const data = await response.json()

  if (!response.ok && response.status !== 503) {
    throw new Error(data.message || `${response.status} ${response.statusText}`)
  }

  return data
}

// POST
export async function apiPost(path, body) {
  const response = await fetch(`${apiUrl}${path}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify(body),
  })

  const data = await response.json()

  if (!response.ok) {
    throw new Error(data.message || `${response.status} ${response.statusText}`)
  }

  return data
}