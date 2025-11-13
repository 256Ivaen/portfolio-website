import type { Metadata } from 'next'

export const metadata: Metadata = {
  title: 'Ivan Odeke - Coming Soon',
  description: 'Personal website and portfolio of Ivan Odeke. Coming soon.',
}

export default function Home() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 to-blue-100 flex items-center justify-center px-4">
      <div className="text-center max-w-2xl mx-auto">
        <div className="mb-8">
          <h1 className="text-4xl md:text-6xl font-bold text-gray-900 mb-4">
            Coming Soon
          </h1>
          <h2 className="text-2xl md:text-3xl font-semibold text-blue-600 mb-6">
            Ivan Odeke
          </h2>
          <p className="text-xl text-gray-600 mb-8">
            Personal Portfolio & Projects
          </p>
          <div className="w-24 h-1 bg-blue-500 mx-auto mb-8"></div>
          <p className="text-lg text-gray-500 mb-8">
            My new website is under construction. I'm working on something amazing!
          </p>
        </div>
        
        <div className="bg-white rounded-lg shadow-lg p-8 max-w-md mx-auto">
          <h3 className="text-xl font-semibold text-gray-800 mb-4">
            Get Notified When I Launch
          </h3>
          <div className="flex flex-col sm:flex-row gap-4">
            <input 
              type="email" 
              placeholder="Enter your email" 
              className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
              Notify Me
            </button>
          </div>
        </div>

        <div className="mt-12 text-gray-500">
          <p>For inquiries, please contact me at:</p>
          <p className="font-medium text-blue-600 mt-2">iodekeivan@gmail.com</p>
        </div>
      </div>
    </div>
  )
}