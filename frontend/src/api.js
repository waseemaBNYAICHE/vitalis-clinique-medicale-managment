// Base URL de l'API Laravel
export const apiUrl =
  import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

function getHeaders(withAuth = true) {
  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  }
  const token = localStorage.getItem('token')
  if (withAuth && token) {
    headers['Authorization'] = `Bearer ${token}`
  }
  return headers
}

// GET
export async function apiGet(path) {
  const response = await fetch(`${apiUrl}${path}`, {
    method: 'GET',
    headers: getHeaders(),
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
    headers: getHeaders(),
    body: JSON.stringify(body),
  })

  const data = await response.json()

  if (!response.ok) {
    throw new Error(data.message || `${response.status} ${response.statusText}`)
  }

  return data
}