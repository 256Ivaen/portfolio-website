#!/bin/bash
echo "🚀 Deploying to VPS..."

# Build static files
npm run build

# Upload to VPS
scp -r out/* root@72.61.7.204:/home/ivanodeke.com/public_html/

echo "✅ Deployment complete! Visit https://ivanodeke.com"