import { Link } from 'react-router-dom';

const PostCard = ({ post }) => {
  return (
    <Link
      to={`/posts/${post.id}`}
      className="block rounded-2xl border shadow-sm p-4 bg-white hover:shadow-md transition"
    >
      <h3>{post.title}</h3>
      <div className="mt-1 text-sm text-gray-500 flex gap-3 flex-wrap">
        {post.date && <span>{post.date}</span>}
        {post.mood && <span>{post.mood}</span>}
      </div>
    </Link>
  )
}

export default PostCard
