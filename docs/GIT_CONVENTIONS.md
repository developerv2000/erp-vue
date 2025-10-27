# 🧭 Git Conventions

This document defines our Git workflow and naming conventions for branches and commits.  
Following these standards keeps our history clean, readable, and consistent across the project.

---

## 🪴 1. Branching Strategy

We use **one branch per task** (feature, bug, fix, or chore).  
Never commit directly to `main`.

### 🔹 Branch Name Format
```
<prefix>/<short-description>
```

### 🔹 Allowed Prefixes

| Prefix | Purpose | Example |
|--------|----------|---------|
| `feature/` | New feature implementation | `feature/login-page` |
| `bugfix/` or `fix/` | Fixing a bug | `bugfix/payment-calculation` |
| `hotfix/` | Urgent fix on production | `hotfix/api-token-expiry` |
| `refactor/` | Code restructuring (no new feature) | `refactor/user-service` |
| `chore/` | Maintenance or configuration work | `chore/update-packages` |
| `test/` | Adding or fixing tests | `test/invoice-service` |
| `docs/` | Documentation-only updates | `docs/git-conventions` |
| `perf/` | Performance improvements | `perf/query-optimization` |
| `style/` | Code style, formatting only | `style/vue-formatting` |
| `ci/` | Continuous integration or deployment | `ci/github-actions` |

### 🔹 Example Branch Workflow
```bash
# Create branch
git checkout -b feature/invoice-export

# Work and commit
git add .
git commit -m "feat(invoice): add export to Excel"

# Push branch to origin
git push -u origin feature/invoice-export
```

After finishing the task:
- Create a Pull Request (PR) to `main`.
- Use **“Squash and Merge”** for clean history.
- Delete the branch after merging.

---

## 🧩 2. Commit Message Format

We follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification:

```
<type>(<optional scope>): <short summary>
```

### 🔹 Allowed Commit Types

| Type | Description | Example |
|------|--------------|----------|
| `feat` | New feature | `feat(auth): add JWT login` |
| `fix` | Bug fix | `fix(invoice): correct total calculation` |
| `chore` | Maintenance / tooling | `chore: update composer dependencies` |
| `refactor` | Code change without feature or bug | `refactor(api): simplify controller logic` |
| `docs` | Documentation changes | `docs(readme): add setup instructions` |
| `style` | Code style / formatting | `style: format with eslint` |
| `test` | Add or fix tests | `test: add user API tests` |
| `perf` | Performance improvements | `perf: cache invoice queries` |
| `build` | Build system or dependency changes | `build: bump vite version` |
| `ci` | CI/CD configuration changes | `ci: update GitHub workflow` |
| `revert` | Reverting a previous commit | `revert: feat(auth): rollback JWT login` |

---

## 🧠 3. Commit Message Guidelines

✅ Use the **imperative mood** (“Add login API”, not “Added login API”).  
✅ Keep the **subject line ≤ 72 characters**.  
✅ The **scope** is optional but encouraged for clarity.  
✅ Add a body if needed for context.

**Example:**
```
feat(invoices): add export to Excel

- Added export button to invoices page
- Integrated with Laravel Excel
```

---

## 🌿 4. Merging

We use **Squash and Merge** for all branches.

### Steps:
1. Ensure your feature branch is up-to-date:
   ```bash
   git fetch origin
   git rebase origin/main
   ```
2. Push changes:
   ```bash
   git push origin feature/your-branch
   ```
3. Create a Pull Request → teammate review → **Squash and Merge**.
4. Write a clean commit message (describing the feature/fix).
5. Delete the branch (local and remote).

---

## ⚡ 5. Handling Conflicts

If merge conflicts occur:
```bash
git status              # See conflicted files
# Edit files manually to resolve conflicts
git add <file>
git commit              # Complete merge
git push origin main
```

---

## 🧹 6. Summary of Useful Commands

| Action | Command |
|--------|----------|
| Create branch | `git checkout -b feature/branch-name` |
| Push branch | `git push -u origin feature/branch-name` |
| Pull latest main | `git pull origin main` |
| Rebase on main | `git rebase origin/main` |
| Merge (squash) | `git merge --squash feature/branch-name` |
| Delete local branch | `git branch -d feature/branch-name` |
| Delete remote branch | `git push origin --delete feature/branch-name` |

---

## 🧭 7. Example Workflow Summary

1. Pull latest main  
2. Create a new branch  
3. Commit your work with proper messages  
4. Push branch  
5. Open PR → Squash and Merge  
6. Delete branch  

---

> **Goal:** Keep `main` clean, readable, and deployable at all times.  
> Every commit on `main` should represent a meaningful change (feature, fix, or improvement).
