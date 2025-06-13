#!/bin/bash

echo "🚀 Setting up Jekyll documentation site for local development..."

# Check if Ruby is installed
if ! command -v ruby &> /dev/null; then
    echo "❌ Ruby not found. Installing Ruby..."
    sudo apt update
    sudo apt install ruby-full build-essential zlib1g-dev -y
fi

# Check Ruby version
echo "✅ Ruby version: $(ruby --version)"

# Install Jekyll and Bundler if not installed
if ! command -v jekyll &> /dev/null; then
    echo "📦 Installing Jekyll and Bundler..."
    gem install jekyll bundler
fi

# Install dependencies
echo "📦 Installing gem dependencies..."
bundle install

echo "🎉 Setup complete!"
echo ""
echo "To start the development server, run:"
echo "  bundle exec jekyll serve"
echo ""
echo "Then visit: http://localhost:4000/spatie-laravel-backup-utils/"
echo ""
echo "For live reload, use:"
echo "  bundle exec jekyll serve --livereload" 