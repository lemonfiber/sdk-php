# Changelog

Generated from the commit history by [git-cliff](https://git-cliff.org) — run
`composer changelog` rather than editing this file.

## Unreleased

### Breaking changes

- *(http)* The token goes to its own stack, through its pin, and nowhere else (#148) **(breaking)** ([`d4c782a`](https://github.com/lemonfiber/sdk-php/commit/d4c782ac3ebb60eef43293dbb2ed3e4b44c10516))
- *(http)* Every call waits at most the wait it was given (#166) **(breaking)** ([`4dc1aa0`](https://github.com/lemonfiber/sdk-php/commit/4dc1aa0cd1ba3c84281b6e835d0f2a21fd365c87))

### Features

- The PHP client, and types generated from the contract ([`ed53148`](https://github.com/lemonfiber/sdk-php/commit/ed53148d4247b9d275746403ae80d18493314066))
- *(contract)* Generate from every kind the server emits (#7) ([`dcb9dae`](https://github.com/lemonfiber/sdk-php/commit/dcb9dae7a49581e53ccba08bc7bf9da83c94aaf4))
- *(contract)* Take the kind a browser-driven setup answers with (#20) ([`e6d7026`](https://github.com/lemonfiber/sdk-php/commit/e6d70268437f1e81ebd5d8d05eb4a8645bb74365))
- *(contract)* Refuse a reference that resolves to nothing (#57) ([`12187e3`](https://github.com/lemonfiber/sdk-php/commit/12187e3d67690856f79cac67975b47aad825a831))
- *(transport)* An address off this machine, and the pin that permits it (#64) ([`c28b7ea`](https://github.com/lemonfiber/sdk-php/commit/c28b7ea88e8a65c3c14d06afe851e54cbf320f92))
- The door that opens without a token (#66) ([`8ea6dbc`](https://github.com/lemonfiber/sdk-php/commit/8ea6dbc35feaa138b7d8c01a1e56567dc3b10c2b))
- The endpoint that answers with a diagnosis (#71) ([`2d2f380`](https://github.com/lemonfiber/sdk-php/commit/2d2f380f25ed1f56dcc9d2caa855e079d6ad56a9))
- The offer and the yes are one request (#73) ([`a68f3ca`](https://github.com/lemonfiber/sdk-php/commit/a68f3cae906183f4c1e3de72fb0a99f0e7ff2ca2))
- The name a long run is answered with (#75) ([`5b92996`](https://github.com/lemonfiber/sdk-php/commit/5b92996662df4759145dc70d50ebcd479b15b02d))
- The five reads a screen has nowhere else to ask (#77) ([`ac4144f`](https://github.com/lemonfiber/sdk-php/commit/ac4144f7acceeeb647a0e8580974c731f2707bd0))
- A window on what the services said (#76) ([`e5f60ff`](https://github.com/lemonfiber/sdk-php/commit/e5f60ff674449feaea393b0c9476397b5cd12187))
- A key for one attempt, and no way to carry it into the next (#84) ([`a8a9331`](https://github.com/lemonfiber/sdk-php/commit/a8a933179d75bd287146cb7f00c992af289beea6))
- *(admission)* The session says who it is for (#93) ([`21388e1`](https://github.com/lemonfiber/sdk-php/commit/21388e1a84547b462428f8cf091843ab11b4be1e))
- *(contract)* The door to what one member can watch (#95) ([`467e748`](https://github.com/lemonfiber/sdk-php/commit/467e748b4ae7ac6b394ba7a8d6744d055a024d8f))
- *(contract)* The door to what the stack is configured to do (#96) ([`976d040`](https://github.com/lemonfiber/sdk-php/commit/976d04077aea83cb6de6c1c35ec1914f94a10758))
- *(contract)* A path for every read the contract page names (#97) ([`7ce7380`](https://github.com/lemonfiber/sdk-php/commit/7ce7380c74f22b21afa727c73b1f710f856c3297))
- *(client)* A read can give a parameter more than once (#130) ([`6fb1a7d`](https://github.com/lemonfiber/sdk-php/commit/6fb1a7d66fbfaf9f5088b4f654a028a8381c17b1))
- *(client)* A pinned peer presenting another certificate is its own problem (#135) ([`a4404ec`](https://github.com/lemonfiber/sdk-php/commit/a4404ec9c4aa9270f4dec751ab739a82f11305dd))
- Fetch a support bundle's bytes (#136) ([`07b895a`](https://github.com/lemonfiber/sdk-php/commit/07b895ad2659d8c15cdb50c5f7dab8d66304755e))
- A refusal carries its whole problem document (#139) ([`ca2d730`](https://github.com/lemonfiber/sdk-php/commit/ca2d73043d5d9dcbda980e367d5d5b010942ff3b))
- *(contract)* Generate RefusalCode and read it on RequestFailed (#159) ([`c11b4bc`](https://github.com/lemonfiber/sdk-php/commit/c11b4bc61533f954a6bb257011ecb56a25220973))
- *(reads)* A read is asked again before a passing failure is reported (#164) ([`96f78ff`](https://github.com/lemonfiber/sdk-php/commit/96f78ff8ed7700167f1262d58b6ffe355db3d769))
- *(contract)* The client holds the path to what is new (#168) ([`e6d692b`](https://github.com/lemonfiber/sdk-php/commit/e6d692ba85d9b67e826ea05c612b85591e77d134))
- *(admission)* A household member's door (#171) ([`bb56a4b`](https://github.com/lemonfiber/sdk-php/commit/bb56a4bf5794fae7575ade7cfbe63a148d560977))
- *(contract)* The doors to the wiring and the installed plugins (#177) ([`f13ff20`](https://github.com/lemonfiber/sdk-php/commit/f13ff20d3b439df5c2e1b06e8afcb547b1eedba8))
- *(contract)* Name the doors to capabilities, keys and setup (#192) ([`81053d3`](https://github.com/lemonfiber/sdk-php/commit/81053d360efabfd360732b613dc140ae7b89a4d9))

### Fixes

- *(contract)* Pin the artefact to a revision that names it (#1) ([`d7f0517`](https://github.com/lemonfiber/sdk-php/commit/d7f0517ff95de3a4683a4b52937a604a3cd3565a))
- *(ci)* Run the gates the other repos already run (#3) ([`a994fcd`](https://github.com/lemonfiber/sdk-php/commit/a994fcd08f5463389f380a30d1bbb24c39347a87))
- *(comments)* Take the requirement identifiers out of the code (#5) ([`9abb319`](https://github.com/lemonfiber/sdk-php/commit/9abb3191473bd258c871a517de14d31866a4822f))
- *(events)* A missed beat is not a broken stream (#8) ([`4257a65`](https://github.com/lemonfiber/sdk-php/commit/4257a653385c20c75b600ef8879e6a61019adf4c))
- *(hooks)* Take the pre-push guard that still refuses the trunk (#14) ([`0f74bb8`](https://github.com/lemonfiber/sdk-php/commit/0f74bb8890093be6b2c19d26e30789b594bb7ba6))
- *(contract)* Tell a warn verdict from a fail one (#21) ([`779f145`](https://github.com/lemonfiber/sdk-php/commit/779f145cce867bb958e65972e36ab5295ccc8696))
- *(client)* Hand the caller the sentence lemonfiber refused with (#22) ([`3dcabdc`](https://github.com/lemonfiber/sdk-php/commit/3dcabdcc846b5bbf1f31619b1ff7439beb0bbdb3))
- *(hooks)* The guard is about branches, and lets a tag through (#29) ([`d5b0c16`](https://github.com/lemonfiber/sdk-php/commit/d5b0c16a0f481846e1236cde8c33d8eb150970db))
- *(hooks)* Permit the one push to the trunk that cannot be reviewed (#43) ([`8103d2c`](https://github.com/lemonfiber/sdk-php/commit/8103d2c55a92e5db734722e1fcb6ef405204c2b3))
- *(guards)* Refuse a directory that holds nothing (#60) ([`29e53a1`](https://github.com/lemonfiber/sdk-php/commit/29e53a1083720e197b2c70987595348eb47ae410))
- *(ci)* A scope that failed is not a "no" (#62) ([`fba1fe0`](https://github.com/lemonfiber/sdk-php/commit/fba1fe0995461d1913b39fde0b01993dce1cb63f))
- Read the ending the way lemonfiber writes one (#68) ([`cba670f`](https://github.com/lemonfiber/sdk-php/commit/cba670f918c89ccdd60980d1a73902aaf06cfd1a))
- *(ci)* Take the alert gate that asks about the analysed commit (#69) ([`7c61d3f`](https://github.com/lemonfiber/sdk-php/commit/7c61d3f9eb0b3b5568c0454cd872d766ffd2b4fe))
- *(ci)* Take the self-test that invents no findings (#70) ([`5fa56de`](https://github.com/lemonfiber/sdk-php/commit/5fa56deac5968b5442f00a0e5f53e92912c4f064))
- *(client)* A request nothing answered is one of this client's problems (#133) ([`234371f`](https://github.com/lemonfiber/sdk-php/commit/234371f065fcf85ff8b2f86ee9a149641cc93c63))
- *(ci)* A check that reads what it tests to the end (#137) ([`00e7012`](https://github.com/lemonfiber/sdk-php/commit/00e7012421e8898246adff3ea5f4de01b95d104b))
- *(events)* Raise a read the connection broke under as an interrupted stream (#165) ([`8e4301f`](https://github.com/lemonfiber/sdk-php/commit/8e4301f9421b27865b84dd3d210bca2d6f87d376))
- *(reads)* Send a boolean parameter as true or false (#197) ([`f18aaba`](https://github.com/lemonfiber/sdk-php/commit/f18aabaec8d452ccceb77708bfa1bf7c064e726b))

### Refactor

- *(scripts)* Let each step answer for one thing (#16) ([`3f4af94`](https://github.com/lemonfiber/sdk-php/commit/3f4af94652d3182c650225fa3b27f650a582a14e))
- *(contract)* Give each step of generation its own way out (#24) ([`8adb2d9`](https://github.com/lemonfiber/sdk-php/commit/8adb2d951529b585d5d2ce2058cf2734d0e8ff3e))
- Hold the generated contract to the line cap (#195) ([`f3d6692`](https://github.com/lemonfiber/sdk-php/commit/f3d669228531dc304e0638ca1b1682d7cecb80af))

### Dependencies

- *(deps)* Bump the actions group across 1 directory with 2 updates (#78) ([`d04168b`](https://github.com/lemonfiber/sdk-php/commit/d04168b639dd8846f5100f0f0deb487bb04f799c))
- *(deps)* Bump the actions group across 1 directory with 2 updates (#101) ([`c00c3ca`](https://github.com/lemonfiber/sdk-php/commit/c00c3caeae8e6fe63aa5ebc20607ba2b7f639290))
- *(deps)* Bump the shared-workflows group across 1 directory with 16 updates (#103) ([`f866c1c`](https://github.com/lemonfiber/sdk-php/commit/f866c1c82df8623bb47211e852603eaad5d305e4))
- *(deps)* Bump the shared-workflows group across 1 directory with 9 updates (#111) ([`6b8a04b`](https://github.com/lemonfiber/sdk-php/commit/6b8a04b674dd4405c07e4b85e4c5801d0d2a69f9))
- *(deps)* Bump the shared-workflows group with 16 updates (#142) ([`10d6f54`](https://github.com/lemonfiber/sdk-php/commit/10d6f54abd54abc691eb47d4deaae283e3ec9d07))
- *(deps)* Bump the actions group across 1 directory with 2 updates (#143) ([`1c000de`](https://github.com/lemonfiber/sdk-php/commit/1c000de0756c063823ba3505d0d97b3bdd2fa90a))
- *(deps)* Bump the shared-workflows group across 1 directory with 16 updates (#151) ([`b8ff429`](https://github.com/lemonfiber/sdk-php/commit/b8ff429b02327ea309a08090fe69d6f0f5544365))
- *(deps)* Bump the shared-workflows group with 16 updates (#163) ([`545e83e`](https://github.com/lemonfiber/sdk-php/commit/545e83e69c09b9906cc378ba65fdef8c8ee8592e))
- *(deps)* Bump the shared-workflows group with 16 updates (#180) ([`7c5d1df`](https://github.com/lemonfiber/sdk-php/commit/7c5d1df7185f0234ff6fd885fc62b04d2b28dcbb))

### Build

- *(deps)* The client takes guzzle 8, which one consumer already had (#65) ([`2da544f`](https://github.com/lemonfiber/sdk-php/commit/2da544fc1601d170269be7628720b632f2a67f5a))
- *(deps)* Take phpstan 2.2.15 and saloon 4.3.0 (#113) ([`a5a837e`](https://github.com/lemonfiber/sdk-php/commit/a5a837ee3eaf26f16b7c443e5f4bd052a9bf0091))
