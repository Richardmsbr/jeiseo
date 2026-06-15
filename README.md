<h1 align="center">JeiSEO</h1>
<h3 align="center">Turn your WordPress into a complete marketing agency</h3>
<p align="center">Automated SEO audits, AI content generation, one-click fixes, and ROI tracking — in a single plugin.</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.0-4361ee?style=flat-square" alt="Version">
  <img src="https://img.shields.io/badge/WordPress-6.0%2B-21759B?style=flat-square&logo=wordpress&logoColor=white" alt="WordPress">
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/license-GPL%20v2-00c853?style=flat-square" alt="License">
  <img src="https://img.shields.io/github/stars/Richardmsbr/jeiseo?style=flat-square&color=f7b500" alt="Stars">
</p>

---

## Why JeiSEO?

Most SEO plugins only **tell you** what's wrong. JeiSEO actually **fixes it**.

It pairs a full technical SEO audit engine with AI-powered content generation (OpenAI **or** Anthropic Claude), so you go from "10 issues found" to "10 issues fixed" and "3 articles published" without leaving your dashboard.

---

## Features

### SEO Audit Engine
Runs a full technical audit of your site and scores its health from 0 to 100:

- SSL / HTTPS verification
- XML Sitemap detection
- `robots.txt` analysis
- Meta title and description checks
- Heading structure analysis (H1, H2)
- Image alt-text verification
- Internal linking analysis
- Mobile responsiveness check
- Page-speed indicators
- Canonical URL validation

### AI Content Generator
- Generate full blog posts from a single keyword
- Choose content length (short / medium / long)
- Configurable tone (professional, casual, friendly, authoritative)
- SEO-optimized structure with proper headings
- Auto-generated meta descriptions and image alt-text
- Save straight to WordPress drafts

### Dashboard
- SEO Health Score (0–100)
- Issues **found vs. fixed** tracking
- Technical status overview
- One-click quick actions
- Usage tracking for the free plan

---

## How it works

```
jeiseo.php                 -> bootstrap, constants, singleton
├── includes/
│   ├── class-jeiseo-api.php       -> AI provider layer (OpenAI + Claude)
│   ├── class-jeiseo-license.php   -> Free / PRO plan management
│   └── class-jeiseo-helpers.php   -> shared utilities
├── modules/
│   ├── audit/    -> SEO audit checks + scoring
│   └── content/  -> AI generation, draft saving, history
└── admin/        -> dashboard, audit, content & settings views
```

The AI layer is provider-agnostic: point it at an **OpenAI** key or an **Anthropic (Claude)** key and the same generation methods (`generate_blog_post`, `generate_meta_description`, `generate_alt_text`) work transparently.

---

## Installation

1. Upload the `jeiseo` folder to `/wp-content/plugins/` (or install the ZIP via **Plugins → Add New → Upload**).
2. Activate **JeiSEO** through the **Plugins** menu.
3. Go to **JeiSEO → Settings** and add your OpenAI or Anthropic API key.
4. Run your first audit and generate your first article.

> The SEO audit works **without** any API key. An API key is only required for the AI content features.

---

## Requirements

| | |
|---|---|
| WordPress | 6.0 or higher |
| PHP | 8.1 or higher |
| AI key | OpenAI **or** Anthropic (Claude) — for AI features only |

---

## Free vs PRO

| | Free | PRO |
|---|------|-----|
| SEO audits | 4 / month | Unlimited |
| AI content generations | 3 / month | Unlimited |
| Basic SEO checks | ✅ | ✅ |
| Dashboard | ✅ | ✅ |
| Auto-fix issues with AI | — | ✅ |
| Weekly email reports | — | ✅ |
| Priority support | — | ✅ |

---

## Screenshots

<!-- Add screenshots of the dashboard, audit results and content generator here:
     ![Dashboard](assets/screenshots/dashboard.png) -->
_Dashboard, audit report and content generator — screenshots coming soon._

---

## Tech

Built on a clean, framework-free WordPress plugin architecture: PHP 8.1 (typed properties and return types), the WordPress AJAX API, and a clear module/admin separation.

---

## License

Released under the **GPL v2 (or later)**. See [LICENSE](LICENSE).

---

## Author

**Richard Sakaguchi** — Solution Architect & AI Engineer

- Website: [sakaguchi.ia.br](https://sakaguchi.ia.br)
- SEO Tools: [seoexpress.com.br](https://seoexpress.com.br)
- HuggingFace: [yoshii-ai](https://huggingface.co/yoshii-ai)
- LinkedIn: [richard-ms](https://www.linkedin.com/in/richard-ms/)
