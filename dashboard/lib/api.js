const READ_TOKEN = process.env.NEXT_PUBLIC_ANALYTICS_READ_TOKEN || 'dev-read-token';

export async function apiGet(path) {
  const res = await fetch(path, {
    headers: { Authorization: `Bearer ${READ_TOKEN}` },
  });
  if (!res.ok) throw new Error(`API ${path} failed: ${res.status}`);
  return res.json();
}