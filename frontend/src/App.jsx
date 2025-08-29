
import React from 'react'
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { ApiProvider } from './context/ApiProvider'
import { PostsProvider } from './context/PostsProvider'
import ListPage from './pages/ListPage.jsx'
import DetailPage from './pages/DetailPage.jsx'

const App = () => {
  return (
    <ApiProvider>
      <PostsProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/" element={<ListPage />} />
            <Route path="/posts/:id" element={<DetailPage />} />
          </Routes>
        </BrowserRouter>
      </PostsProvider>
    </ApiProvider>
  )
}

export default App
