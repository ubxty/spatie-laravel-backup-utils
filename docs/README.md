# Laravel Backup Utils Documentation

This directory contains the documentation for Laravel Backup Utils, built with Jekyll and hosted on GitHub Pages.

## Local Development

To run the documentation site locally:

1. Install Ruby and Bundler
2. Install dependencies:
   ```bash
   bundle install
   ```
3. Start the local server:
   ```bash
   bundle exec jekyll serve
   ```
4. Visit `http://localhost:4000` in your browser

## Documentation Structure

- `_config.yml` - Jekyll configuration
- `_layouts/` - Page layouts
- `_includes/` - Reusable components
- `assets/` - Static assets (CSS, JS, images)
- `v1/` - Version 1 documentation
  - `introduction.md`
  - `installation.md`
  - `configuration.md`
  - `usage.md`

## Adding New Documentation

1. Create a new markdown file in the appropriate version directory
2. Add front matter at the top of the file:
   ```yaml
   ---
   title: Your Page Title
   order: 5  # Navigation order
   ---
   ```
3. Write your documentation in Markdown
4. Add the page to the navigation in `_config.yml`

## Building for Production

The documentation is automatically built and deployed to GitHub Pages when changes are pushed to the main branch. You can also manually trigger a build from the GitHub Actions tab.

## Contributing

1. Fork the repository
2. Create a new branch
3. Make your changes
4. Submit a pull request

## License

The documentation is licensed under the MIT License - see the [LICENSE](../LICENSE) file for details. 