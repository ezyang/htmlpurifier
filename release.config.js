module.exports = {
  debug: true,
  branch: 'master',
  plugins: [
    '@semantic-release/commit-analyzer',
    '@semantic-release/release-notes-generator',
    [
      '@semantic-release/changelog',
      {
        'changelogFile': 'NEWS'
      }
    ],
    [
      '@semantic-release/exec',
      {
        'prepareCmd': 'php update-for-release ${nextRelease.version}'
      }
    ],
    [
      '@semantic-release/git',
      {
        'assets': [
          'VERSION',
          'NEWS',
          'Doxyfile',
          ['library/**/*', '!library/standalone/**/*', '!library/HTMLPurifier.standalone.php'],
          'configdoc/**/*',
        ],
      }
    ],
    '@semantic-release/github'
  ],
}
