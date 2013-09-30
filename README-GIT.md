Open Reading (Based on Pi Engine) Github Usage
========================

Notes:
* ```upstream```: Open Reading Repo at ```https://github.com/lqsdev/open-reading```
* ```origin``` or ```your repo```: the repo you create by forking Open Reading at ```https://github.com/<your-account>/open-reading```
* ```local repo```: the working repo you clone from your repo 

Checkout from Open Reading project (readonly)
------------------------------------------
* ```git clone git://github.com/lqsdev/open-reading```

Make a new fork
---------------
* Fork from [Open Reading Repo](https://github.com/lqsdev/open-reading) following ![Image guide](https://raw.github.com/open-reading-asset/image/master/git-fork.png)


Working with forked repo
------------------------
* Checkout code to local computer as working repo: ```git clone https://github.com/<your-account>/open-reading```
* Working with commits
  * Synchronize code from your repo: ```git pull``` or ```git fetch```
  * Add local changes: ```git add --all```
  * Commit local changes: ```git commit -a -m 'Commit log message.'```
  * Push commits to your repo: ```git push```
* Working with branches
  * Check local branches: ```git branch```
  * Create a local branch: ```git branch -a <new-branch>```
  * Push a local branch to your repo: ```git push```
  * Swtich to a branch: ```git checkout <another-branch>```
  * Merge code from another branch: ```git merge <another-branch>```
  * Delete a local branch: ```git branch -d <old-branch>```
  * Delete a branch from your repo: ```git push origin :<old-branch>```
* Working with tags
  * Check local branches: ```git tag```
  * Create a local branch: ```git tag -a <new-tag>```
  * Push local tags to your repo: ```git push --tags```
  * Delete a local branch: ```git tag -d <old-tag>```
  * Delete a tag from your repo: ```git push origin :<old-tag>```

Working with upstream repo
--------------------------
* Add Open Reading Repo as upstream: ```git remote add upstream https://github.com/lqsdev/open-reading.git```
* Fetch changes from Open Reading Repo: ```git fetch upstream```
* Merge Open Reading changes into local repo: ```git merge upstream/<branch-name>```
* Synchronize your repo with Open Reading Repo: ```git merge upstream/<branch-name>``` + ```git push origin <branch-name>```

