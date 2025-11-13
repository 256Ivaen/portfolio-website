import type { Metadata } from 'next'

export const metadata: Metadata = {
  title: 'Ivan Odeke - Coming Soon',
  description: 'Personal website and portfolio of Ivan Odeke. Coming soon.',
}

export default function Home() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-white">
      <div className="text-center text-gray-900 max-w-md mx-auto px-4">
        <h1 className="text-6xl font-light mb-2">Coming Soon</h1>
        <div className="w-20 h-1 bg-blue-500 mx-auto mb-4"></div>
        <h2 className="text-2xl font-light mb-4">Ivan Odeke</h2>
        
        <p className="text-gray-600 leading-relaxed mb-8">
          My personal website is under construction. 
          Something amazing is on the way!
        </p>

        <div className="space-y-4">
          <a 
            href="mailto:iodekeivan@gmail.com"
            className="inline-flex items-center justify-center bg-blue-500 text-white px-6 py-3 rounded-full hover:bg-blue-600 transition-colors font-light text-sm uppercase w-full"
          >
            Contact Me
          </a>
        </div>
      </div>
    </div>
  )
}