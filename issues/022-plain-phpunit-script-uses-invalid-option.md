# Issue 022 — The plain test command uses an unsupported PHPUnit option

**Severity:** Low  
**Status:** Open; proposed fix only  
**Reviewed commit:** `9c7229442fa48a8edd66898d970ace71e9bea17a`

## Evidence and reproduction

`composer.json` defines `test:plain` as `phpunit --testdox=false`. The installed PHPUnit 9.6.36 treats `--testdox` as a flag, not an option accepting `false`.

```bash
composer run test:plain
```

The underlying command exits before running tests with `Option "--testdox" does not allow an argument`. Simply removing the argument would enable TestDox, and just using `phpunit` still enables it through `phpunit.xml`'s `testdox="true"`. This issue is distinct from the Composer metadata validation problem and from issue 005's successful-login test dependencies.

## Fix with code

Replace only the `test:plain` script value, preserving the other scripts:

```json
"test:plain": "phpunit --no-configuration --bootstrap tests/bootstrap.php --colors=always --fail-on-warning --fail-on-risky tests"
```

`--no-configuration` avoids the TestDox setting. The explicit bootstrap and test directory preserve test loading; the warning/risky flags preserve those protections from the normal configuration.

## Verify

```bash
composer run test:plain
composer test
```

The first command should display ordinary test progress and a summary; the second should retain the normal TestDox output. Both must run the same 72 current tests rather than stopping at option parsing. Future added tests should appear in both commands.

**Validation performed during analysis:** reproduced the invalid option and ran the corrected underlying command successfully in the reviewed snapshot: 72 tests, 260 assertions. No application code change is required.
