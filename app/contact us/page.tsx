import Link from 'next/link'
import { Home, ArrowLeft } from 'lucide-react'

export default function NotFound() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-white">
      <div className="text-center text-gray-900 max-w-md mx-auto px-4">
        <h1 className="text-8xl font-light mb-2">404</h1>
        <div className="w-20 h-1 bg-primary mx-auto mb-4"></div>
        <h2 className="text-2xl font-light mb-4">Page Not Found</h2>
        
        <p className="text-gray-600 leading-relaxed mb-8">
          The page you're looking for doesn't exist or has been moved.
        </p>

        <div className="space-y-4">
          <Link 
            href="/"
            className="inline-flex items-center justify-center bg-primary text-white px-6 py-3 rounded-full hover:bg-primary/90 transition-colors font-light text-sm uppercase w-full"
          >
            Back to Homepage
          </Link>
        </div>
      </div>
    </div>
  )
}