# Local Development Setup

This guide will help you run the Spatie Laravel Backup Utils documentation site locally.

## 🚀 Quick Setup

### Option 1: Automated Setup
```bash
# Run the setup script
cd docs
./setup-local.sh
```

### Option 2: Manual Setup

#### 1. Install Ruby (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install ruby-full build-essential zlib1g-dev -y
```

#### 2. Install Jekyll and Bundler
```bash
gem install jekyll bundler
```

#### 3. Install Dependencies
```bash
cd docs
bundle install
```

#### 4. Start Development Server
```bash
bundle exec jekyll serve
```

## 🌐 Accessing Your Site

After running `bundle exec jekyll serve`, your site will be available at:

- **Local URL**: `http://localhost:4000/spatie-laravel-backup-utils/`
- **With live reload**: `bundle exec jekyll serve --livereload`
- **All interfaces**: `bundle exec jekyll serve --host 0.0.0.0` (access from other devices)

## 🛠 Development Commands

### Basic Commands
```bash
# Start development server
bundle exec jekyll serve

# Build static site
bundle exec jekyll build

# Clean build files
bundle exec jekyll clean

# Serve with live reload (auto-refresh browser)
bundle exec jekyll serve --livereload

# Serve with drafts
bundle exec jekyll serve --drafts

# Verbose output for debugging
bundle exec jekyll serve --verbose
```

### Advanced Commands
```bash
# Serve on specific port
bundle exec jekyll serve --port 4001

# Serve without baseurl (for easier local development)
bundle exec jekyll serve --baseurl ''

# Incremental builds (faster rebuilds)
bundle exec jekyll serve --incremental

# Force polling (useful for Windows/WSL)
bundle exec jekyll serve --force_polling
```

## 📁 File Structure

```
docs/
├── _config.yml           # Jekyll configuration
├── _layouts/             # Page layouts
│   └── default.html      # Main layout template
├── _includes/            # Reusable components
│   ├── header.html       # Page headers
│   └── sidebar.html      # Navigation sidebar
├── assets/               # Static assets
│   ├── css/
│   │   └── style.css     # Main stylesheet
│   ├── js/
│   │   └── main.js       # JavaScript functionality
│   └── images/           # Images and meta assets
├── v1/                   # Version 1 documentation
│   ├── introduction.md
│   ├── installation.md
│   ├── configuration.md
│   └── usage.md
├── index.md              # Homepage
└── Gemfile               # Ruby dependencies
```

## 🎨 Customization

### Updating Styles
1. Edit `assets/css/style.css`
2. Changes will auto-reload with `--livereload` flag

### Adding New Pages
1. Create new `.md` file in appropriate directory
2. Add front matter:
   ```yaml
   ---
   title: Your Page Title
   order: 5
   permalink: /v1/your-page/
   ---
   ```
3. Add to navigation in `_config.yml`

### Modifying Layout
1. Edit `_layouts/default.html`
2. Update `_includes/` components as needed

## 🐛 Troubleshooting

### Common Issues

**Port already in use:**
```bash
bundle exec jekyll serve --port 4001
```

**Permission errors:**
```bash
# Install gems in user directory
bundle config set --local path 'vendor/bundle'
bundle install
```

**Slow builds:**
```bash
# Use incremental builds
bundle exec jekyll serve --incremental
```

**Ruby version issues:**
```bash
# Check Ruby version
ruby --version

# Install specific Ruby version with rbenv
curl -fsSL https://github.com/rbenv/rbenv-installer/raw/HEAD/bin/rbenv-installer | bash
rbenv install 3.1.0
rbenv global 3.1.0
```

**Bundle issues:**
```bash
# Clean and reinstall
rm -rf vendor/bundle
bundle install
```

### Debug Mode
```bash
# Run with verbose output
bundle exec jekyll serve --verbose --trace
```

## 📝 Making Changes

### Content Updates
1. Edit `.md` files in `v1/` directory
2. Changes auto-reload with live reload enabled
3. Check `http://localhost:4000/spatie-laravel-backup-utils/`

### Style Updates
1. Edit `assets/css/style.css`
2. Browser will auto-refresh with `--livereload`

### Configuration Changes
1. Edit `_config.yml`
2. **Restart server** (config changes require restart)

## 🚀 Building for Production

```bash
# Build static site
JEKYLL_ENV=production bundle exec jekyll build

# Output will be in _site/ directory
ls -la _site/
```

## 📱 Testing Meta Images

Your Open Graph images can be tested at:
- **Facebook Debugger**: https://developers.facebook.com/tools/debug/
- **Twitter Card Validator**: https://cards-dev.twitter.com/validator
- **LinkedIn Inspector**: https://www.linkedin.com/post-inspector/

## 💡 Tips

1. **Use live reload** for faster development
2. **Test on mobile** using `--host 0.0.0.0`
3. **Check builds locally** before pushing to GitHub
4. **Test meta tags** with social media validators
5. **Use incremental builds** for large sites

## 🆘 Need Help?

If you run into issues:
1. Check the [Jekyll documentation](https://jekyllrb.com/docs/)
2. Review the `_config.yml` settings
3. Check Ruby and gem versions
4. Try clearing the cache: `bundle exec jekyll clean` 