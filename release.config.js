module.exports = {
  debug: true,
  branch: 'master',
  plugins: [
    '@semantic-release/commit-analyzer',
    '@semantic-release/release-notes-generator',
    ['@semantic-release/changelog', {'changelogFile': 'NEWS'}],
    '@semantic-release/exec',
    ['@semantic-release/git', {
      'assets': ['VERSION', 'NEWS', 'Doxyfile', 'library/HTMLPurifier.php', 'library/HTMLPurifier/Config.php', 'library/HTMLPurifier.includes.php'],
    }],
    '@semantic-release/github'
  ],
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
