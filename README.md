# Imexso

Monorepo layout:

- **`imexso-frontend-main/`** — Next.js app (run `npm install` / `npm run dev` inside `imexso-frontend-main/imexso-frontend-main/`, or use wrapper scripts in `imexso-frontend-main/package.json`).
- **`imexso-main/`** — Laravel API (see `imexso-main/imexso-main/`).

Copy `.env` / `.env.local` from your secure storage; they are not committed.

## Publish to GitHub (from this folder)

Git is already initialized on branch `main` with an initial commit. Create an **empty** repository on GitHub (no README, no `.gitignore` template), then:

```bash
cd /home/milos/Downloads/imexso-frontend-main
git remote add origin https://github.com/<YOUR_GITHUB_USERNAME>/<REPO_NAME>.git
git push -u origin main
```

Use your real username and repo name (for example `imexso`). For SSH, use `git@github.com:<YOUR_GITHUB_USERNAME>/<REPO_NAME>.git` instead.

If Git complains about the commit author, set your identity once, then amend:

```bash
git config user.name "Your Name"
git config user.email "your.email@example.com"
git commit --amend --reset-author --no-edit
git push -u origin main
```

Optional: install [GitHub CLI](https://cli.github.com/) (`gh`), run `gh auth login`, then `gh repo create imexso --private --source=. --remote=origin --push` from this directory to create the remote and push in one step.
