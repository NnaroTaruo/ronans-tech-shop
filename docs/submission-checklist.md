# Submission checklist - Ronan Antoque

Deadline supplied by Ronan: **November 12, 2026**. The exact cutoff time comes from the assessment email; no time is invented here. Reference: assessment PDF sections 17-19. A local working app plus video is acceptable. Sending/public deployment is not performed automatically.

## Prepared in the project

- [x] Customer and admin implementation, five-table schema and repeatable seed.
- [x] README: overview, stack, storage/limits, Windows setup, ignored local config/example placeholders, seed/reset, routes and fictional demo credentials.
- [x] Test log with ten separate Pass / Fail / Not tested rows and evidence.
- [x] System-flow/data-model explanation and truthful assistance/asset disclosure.
- [x] `demo-guide.md`: timed 3-5 minute purchase/admin script and rehearsal steps.
- [x] `submission-email.md`: correct PDF recipient/subject, access instructions and link placeholders.
- [x] `known-issues.md`: limitations and remaining delivery actions.

See `evidence/README.md` for the actual screenshot inventory and browser-test scope. Test Pass results are verified execution, not a claim Ronan personally ran every case or recorded a video.

## Ronan's final actions

- [ ] Rehearse the local demo once using `demo-guide.md`; confirm microphone/screen text and the required product prices/stock.
- [ ] Record and watch the **3-5 minute** purchase/admin video. Upload it and verify viewing permissions in a private window.
- [ ] Create or select a GitHub repository and upload/commit the shareable project. This project currently has no GitHub remote or publication; a local Git folder alone does not satisfy the repository requirement.
- [ ] Verify the repository contains the README, source, schema/seed, compiled CSS, docs, test log and screenshots. Exclude `config/local.php`, `tmp/`, session files, `node_modules/`, private data and database exports. `.gitignore` covers the local/private working paths.
- [ ] If private, obtain the reviewer username and grant access. If public, review the files before publishing. A repository URL is distinct from a hosted application URL.
- [ ] In the GitHub web interface, check images render and setup/source files are readable. Follow README setup in a separate folder if time permits; do not overwrite the working local config/database.
- [ ] Replace **[REPOSITORY URL]** and **[REVIEWER-ACCESSIBLE VIDEO URL]** in the email draft. Adjust screenshot branch/path. No placeholder may remain in the sent email.
- [ ] Open repository/video links without relying on your logged-in session. Localhost and `C:\...` paths are not reviewer-accessible public links.
- [ ] Send the reviewed email yourself to **admin@sales-cubetech.cloud**, subject **Web Development Intern Assessment - Ronan Antoque**, before the email's exact deadline.
- [ ] Retain the sent message and verify attachments/links if used. Keep a local copy of source, screenshots and video.

## GitHub upload choices

Use the GitHub workflow you already know. If you use the website, create a repository, choose **Add file -> Upload files**, and upload the shareable project's contents with its folder structure. Upload source/docs/screenshots, not only the ZIP file. Commit the upload, then copy the repository page URL. Do not upload your working `config/local.php`, `tmp`, `.git` or `node_modules` folders.

If the web uploader limits a batch, upload source/documentation first and `docs/evidence/screenshots/` in a second batch. Preserve that folder path. The prepared ZIP is a convenient clean copy to extract elsewhere before uploading; it is not a replacement for browsable source files in the repository.

If using Git from PowerShell, first review `.gitignore` and `git status`; add only intended project files, commit, add your actual repository URL as `origin`, then push the current branch. No account, repository URL, branch rename or remote is assumed by this project. Never paste an access token into documentation or command history.
