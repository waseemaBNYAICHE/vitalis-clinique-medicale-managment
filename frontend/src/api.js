// Single place the API base URL is read from. Set via VITE_API_URL
// (docker-compose.yml, or frontend/.env when running outside Docker).
export const apiUrl = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

export async function apiGet(path) {
  const response = await fetch(`${apiUrl}${path}`, {
    headers: { Accept: 'application/json' },
  })

  if (!response.ok && response.status !== 503) {
    throw new Error(`${response.status} ${response.statusText}`)
  }

  return response.json()
}
