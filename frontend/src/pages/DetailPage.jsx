import React from 'react'
import { Link, useParams } from 'react-router-dom'
import { usePosts } from '../context/PostsProvider'
import { useEffect, useState } from 'react'


const DetailPage = () => {
  const { id } = useParams();
  const { getPostById } = usePosts();
  const [data, setData] = useState(null)

  useEffect(() => {
    getPostById(Number(id))
      .then(setData)
      .catch((e) => { console.error(e); setData({ ok: false }) })
  }, [id, getPostById])

  if (!data) return null

  if (data.ok === false) {
    return <div className="max-w-3xl mx-auto px-4 py-10 text-red-600">Errore nel caricamento.</div>
  }

  const post = data.post || {}
  const tags = data.tags || []
  const media = data.media || []

  return (
    <div className="max-w-3xl mx-auto px-4 py-10">
      <Link to="/" className="text-indigo-600 hover:text-indigo-700 font-medium">← Torna alla lista</Link>

      <h1 className="mt-3 text-3xl font-bold">{post.title}</h1>

      <div className="mt-2 text-sm text-gray-500 flex flex-wrap gap-3">
        <span>{post.date}</span>
        {post.mood && (
          <span className="inline-flex items-center gap-2">
            <span className="h-2 w-2 rounded-full bg-emerald-500 inline-block" />
            {post.mood}
          </span>
        )}
        {post.placeName && <span>{post.placeName}</span>}
        {typeof post.costEUR === 'number' && <span>€{post.costEUR.toFixed(2)}</span>}
      </div>

      <p className="mt-6 whitespace-pre-wrap leading-relaxed text-gray-800">
        {post.description}
      </p>

      {tags.length > 0 && (
        <>
          <h2 className="mt-8 text-lg font-semibold">Tag</h2>
          <div className="mt-3 flex flex-wrap gap-2">
            {tags.map((t) => (
              <span key={t} className="px-3 py-1 rounded-full bg-gray-100 text-gray-700 border">
                #{t}
              </span>
            ))}
          </div>
        </>
      )}

      {media.length > 0 && (
        <>
          <h2 className="mt-8 text-lg font-semibold">Media</h2>
          <ul className="mt-3 space-y-2">
            {media.map((m, i) => (
              <li key={i} className="text-indigo-600 underline break-all">
                <a href={m.url} target="_blank" rel="noreferrer">
                  {m.type} — {m.url}
                </a>
              </li>
            ))}
          </ul>
        </>
      )}
    </div>
  )
}

export default DetailPage
