module.exports = {
  debug: true,
  branch: 'master',
  verifyConditions: [
    '@semantic-release/changelog',
    '@semantic-release/github',
  ],
  prepare: [
    {
      path: '@semantic-release/exec',
      cmd: 'php update-for-release ${nextRelease.version}'
    },
    '@semantic-release/changelog',
    '@semantic-release/git',
  ],
  publish: [
    '@semantic-release/github',
  ]
}
