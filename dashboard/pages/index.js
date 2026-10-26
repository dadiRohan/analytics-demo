import { useEffect, useState } from 'react';
import { apiGet } from '../lib/api';
import {
  LineChart, Line, XAxis, YAxis, Tooltip, ResponsiveContainer,
  CartesianGrid, BarChart, Bar,
} from 'recharts';

function day(offset) {
  const d = new Date();
  d.setDate(d.getDate() + offset);
  return d.toISOString().slice(0, 10);
}

export default function Home() {
  const [from, setFrom] = useState(day(-6));
  const [to,   setTo]   = useState(day(0));
  const [overview, setOverview] = useState(null);
  const [trend,    setTrend]    = useState([]);
  const [pages,    setPages]    = useState([]);
  const [events,   setEvents]   = useState([]);
  const [error,    setError]    = useState(null);

  useEffect(() => {
    const q = `?from=${from}&to=${to}`;
    setError(null);
    Promise.all([
      apiGet('/api/analytics/overview' + q),
      apiGet('/api/analytics/trend' + q + '&event_name=page_view'),
      apiGet('/api/analytics/popular-pages' + q),
      apiGet('/api/analytics/events' + q),
    ]).then(([ov, tr, pg, ev]) => {
      setOverview(ov); setTrend(tr); setPages(pg); setEvents(ev);
    }).catch(setError);
  }, [from, to]);

  return (
    <div style={{ fontFamily: 'system-ui, sans-serif', maxWidth: 960, margin: '2rem auto', padding: '0 1rem' }}>
      <h1>Analytics Dashboard</h1>

      <div style={{ marginBottom: '1rem' }}>
        <label>From: <input type="date" value={from} onChange={e => setFrom(e.target.value)} /></label>
        <label style={{ marginLeft: '1rem' }}>To: <input type="date" value={to} onChange={e => setTo(e.target.value)} /></label>
      </div>

      {error && <p style={{ color: 'crimson' }}>{error.message}</p>}

      {overview && (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '1rem', margin: '1rem 0' }}>
          <Card label="Page Views"      value={overview.page_views} />
          <Card label="Unique Visitors" value={overview.unique_visitors} />
          <Card label="Sessions"        value={overview.sessions} />
        </div>
      )}

      <h2>Page Views Trend</h2>
      <ResponsiveContainer width="100%" height={260}>
        <LineChart data={trend}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis allowDecimals={false} />
          <Tooltip />
          <Line type="monotone" dataKey="count" stroke="#3b82f6" strokeWidth={2} />
        </LineChart>
      </ResponsiveContainer>

      <h2>Popular Pages</h2>
      <ResponsiveContainer width="100%" height={260}>
        <BarChart data={pages}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="page" />
          <YAxis allowDecimals={false} />
          <Tooltip />
          <Bar dataKey="views" fill="#10b981" />
        </BarChart>
      </ResponsiveContainer>

      <h2>Event Breakdown</h2>
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            <th align="left">Event</th>
            <th align="right">Count</th>
          </tr>
        </thead>
        <tbody>
          {events.map(e => (
            <tr key={e.event_name}>
              <td>{e.event_name}</td>
              <td align="right">{e.count}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function Card({ label, value }) {
  return (
    <div style={{ padding: '1rem', borderRadius: 8, background: '#f3f4f6' }}>
      <div style={{ fontSize: 12, color: '#6b7280', textTransform: 'uppercase' }}>{label}</div>
      <div style={{ fontSize: 28, fontWeight: 700 }}>{value}</div>
    </div>
  );
}