import React, { createContext, useContext, useMemo } from 'react'
import axios from 'axios'

const ApiContext = createContext(null)

export function ApiProvider({ children }) {
  const baseURL = import.meta.env.VITE_API_BASE ?? 'http://localhost:8000/api'

  const client = useMemo(() => {
    const c = axios.create({
      baseURL,
      withCredentials: true,
      headers: { 'Content-Type': 'application/json' },
    })

    return c
  }, [baseURL])

  return (
    <ApiContext.Provider value={client}>
      {children}
    </ApiContext.Provider>
  )
}

export function useApi() {
  const ctx = useContext(ApiContext)
  if (!ctx) throw new Error('useApi deve essere usato dentro <ApiProvider>')
  return ctx
}
