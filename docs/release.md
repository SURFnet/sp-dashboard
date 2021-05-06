# Release guide

The release procedure is fairly simple:
1. Determine the release number according to [semantic versioning](https://semver.org/)
2. Update [the changelog](../CHANGELOG.md).  We prefer to categorize our entries by type (bug, feature, chore, security, ... ).  You can look back through the file to see what fits.<br />
   **Note:** if you want to make this easier, update the changelog along with the PR you merge.
3. Start the git release proces:
   - if needed, create a release branch.  Anything which is a minor release gets its own branch.  So practically speaking: release/4.0, release/4.1 but not release/4.1.1.  4.1.1 is simply merged into release/4.1.
   - merge the develop branch into the release branch.
   - merge the release branch into the master branch.
     **Note:** as you're merging into the master branch this requires you to have administrative priviliges.  You can go ahead and merge without approval (providing the develop branch was green).
   - [create a new release on github](https://github.com/SURFnet/sp-dashboard/releases), tagging the master branch with the new version number.

## Hands on example:

1. We write the changelog entries on every PR, or alternatively write it right before launching the release on the `develop` branch. The current Changelog shows some examples on how we like to write them. In essence the PR title (including the PR sequence number) should already be descriptive enough to use as a changelog entry. On larger releases we like to categorize them by type: bug, feature, chore, ...
2. Merge to the release branch. We follow semantic versioning so please consider what kind of release number will be used. When a new minor release is tagged, open a new release branch. We follow this format for naming the branch: `release/4.2` All successive 4.2.x releases will be merged to that branch.
3. Next, when a new release is created, the release branch is merged to `master`. When dealing with a backport release. Only merge the change to the older release branch, say: `release/3.9`.
4. From the master branch (or in case of a backport release, on the release branch itself) we create a tag. Tags do not include the `v` prefix.
5. Finally in Github we copy/paste the release notes from the changelog onto the newly created release tag.

Tying it all together for a new release:

 ```bash
$ git fetch
$ git checkout develop # Check to see if you need to pull remote changes to develop
$ vi CHANGELOG.md
$ git commit -a
$ git push
$ git checkout -b release/4.2
$ git push origin release/4.2
$ git checkout master # See if you need to pull latest changes from origin/master
$ git merge release/4.2
$ git push
$ git tag 4.2.0
$ git push --tags
```
