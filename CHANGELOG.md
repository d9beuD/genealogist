# Changelog

## [2.4.1](https://github.com/d9beuD/genealogist/compare/genealogist-v2.4.0...genealogist-v2.4.1) (2026-04-12)


### Bug Fixes

* **person:** Move edit action to profile sheet ([7f8e807](https://github.com/d9beuD/genealogist/commit/7f8e807a2299049c44f1eb8e5d791141b0802336))


### Miscellaneous

* **agents:** rename .agent directory to .agents ([cdb9cd3](https://github.com/d9beuD/genealogist/commit/cdb9cd3795760e8b1c1521d4cd22f1415f76f681))

## [2.4.0](https://github.com/d9beuD/genealogist/compare/genealogist-v2.3.2...genealogist-v2.4.0) (2026-04-12)


### Features

* **union:** add end date fields ([25dda8f](https://github.com/d9beuD/genealogist/commit/25dda8f1233c0ebcfa6d5f89ce15f02b55bb6d09))


### Bug Fixes

* **doctrine:** remove deprecated controller resolver auto mapping ([b395dae](https://github.com/d9beuD/genealogist/commit/b395dae7b6cf62b1b999c97bf14127346ec2d7e2))
* **form:** use named constraint arguments ([153feeb](https://github.com/d9beuD/genealogist/commit/153feebe039cc20a26d0b269a0a138ccc7f5fc0a))
* **person:** redirect after profile updates ([c8d45ff](https://github.com/d9beuD/genealogist/commit/c8d45ffb5a83ace0a34949d13c1ce10832a47797))
* **routing:** use Symfony route attributes ([52a86f1](https://github.com/d9beuD/genealogist/commit/52a86f17764dfd3735649d412b69adeca6b77468))
* **security:** add Symfony 8-compatible voter signatures ([0eb9638](https://github.com/d9beuD/genealogist/commit/0eb96386475ccdd949ea393f6d23b9c7d07438d2))
* **source:** allow inline editing from member profiles ([0debcad](https://github.com/d9beuD/genealogist/commit/0debcaddff72e551660b185cd5723d575c597a15))
* **source:** restore edit button sizing ([fc7d003](https://github.com/d9beuD/genealogist/commit/fc7d003a7b0388489db8e247627dadf5d7610450))
* **tree:** align owner accessors with renamed property ([e8e4a4f](https://github.com/d9beuD/genealogist/commit/e8e4a4f53ef40c67f6b0427c08efc1c7643374d7))
* **twig:** read route name from request attributes ([c5aba45](https://github.com/d9beuD/genealogist/commit/c5aba454ace51a3a647b36598724177f0f8e30f7))
* **validator:** use named NotBlank arguments ([25d78a4](https://github.com/d9beuD/genealogist/commit/25d78a4494bad84c1b697188d790f491f525a701))


### Refactoring

* apply AddArrayFunctionClosureParamTypeRector ([74b6508](https://github.com/d9beuD/genealogist/commit/74b6508ed81ddb22626c1c4140cfe58ce94d4993))
* apply AddArrowFunctionParamArrayWhereDimFetchRector ([f46a6c2](https://github.com/d9beuD/genealogist/commit/f46a6c226daabfbaec32e2d9490d8636fea36373))
* apply AddArrowFunctionReturnTypeRector ([9223f99](https://github.com/d9beuD/genealogist/commit/9223f9908e490baae989650459900796c8c8f5fe))
* apply AddVoidReturnTypeWhereNoReturnRector ([80a508a](https://github.com/d9beuD/genealogist/commit/80a508a19ce74662d7d87cfeb52341fc0c1654c9))
* apply AttributeKeyToClassConstFetchRector ([08e8ec1](https://github.com/d9beuD/genealogist/commit/08e8ec1e5984b840dc4dbc399f18867466333279))
* apply CatchExceptionNameMatchingTypeRector ([a8e66f1](https://github.com/d9beuD/genealogist/commit/a8e66f18ad1b85da0a9a7f6b9472ef483f785d9a))
* apply ChangeSwitchToMatchRector ([f60555d](https://github.com/d9beuD/genealogist/commit/f60555d3bcd4d347eb25ee49cf2e1f79fbc5e7d7))
* apply ClosureReturnTypeRector ([5f27eae](https://github.com/d9beuD/genealogist/commit/5f27eaeb528b89cf1314325f2b970383ddc604d0))
* apply ClosureToArrowFunctionRector ([846318e](https://github.com/d9beuD/genealogist/commit/846318e63766e67100fcc1febeb25dfb6a3b56a5))
* apply CombineIfRector ([0c8b9cc](https://github.com/d9beuD/genealogist/commit/0c8b9cc52fdd7d47450abd55cee4f974b86f1cf9))
* apply ControllerMethodInjectionToConstructorRector ([8f6cc2a](https://github.com/d9beuD/genealogist/commit/8f6cc2a8600324943628416eac4b206fd5875e41))
* apply DocblockReturnArrayFromDirectArrayInstanceRector ([d9afd0b](https://github.com/d9beuD/genealogist/commit/d9afd0b03b58d3a3c98c2bb6bbb195dea2745dcd))
* apply ExplicitBoolCompareRector ([93d07a3](https://github.com/d9beuD/genealogist/commit/93d07a3afa6e3068d9147605f0075b049b69dc9f))
* apply FlipTypeControlToUseExclusiveTypeRector ([a76b5cf](https://github.com/d9beuD/genealogist/commit/a76b5cff3aa8be3d7b78a709543105c4d524738a))
* apply InlineClassRoutePrefixRector ([9b69df5](https://github.com/d9beuD/genealogist/commit/9b69df5be7343cc339ab2fe1fec1786a7be27c48))
* apply InlineConstructorDefaultToPropertyRector ([b9b8233](https://github.com/d9beuD/genealogist/commit/b9b82337f1f244146426927fcb18049b32263ba3))
* apply JoinStringConcatRector ([ced5f71](https://github.com/d9beuD/genealogist/commit/ced5f7100c5f3d42bd9cf0b2195faa16d78596cc))
* apply LiteralGetToRequestClassConstantRector ([a03e931](https://github.com/d9beuD/genealogist/commit/a03e9311b97198bc05f8fd14dfe5e2c5f60b543c))
* apply NewlineAfterStatementRector ([737c8ec](https://github.com/d9beuD/genealogist/commit/737c8ec67500daa33c9d1d6104f72c926f926885))
* apply NewlineBeforeNewAssignSetRector ([5ad2991](https://github.com/d9beuD/genealogist/commit/5ad29919d0f81733a9515b33da3095503eb5bf26))
* apply NullToStrictStringFuncCallArgRector ([c88d80b](https://github.com/d9beuD/genealogist/commit/c88d80b0191d57bfea8642dd3e609a6a2c5f9fba))
* apply ReadOnlyPropertyRector ([8c58244](https://github.com/d9beuD/genealogist/commit/8c582446a3c32a0078df487467105388c7b95a10))
* apply RemoveEmptyClassMethodRector ([84c96ea](https://github.com/d9beuD/genealogist/commit/84c96eafa6b9693eb3546e519fa5b0301ed4d57b))
* apply RemoveParentDelegatingConstructorRector ([96ea205](https://github.com/d9beuD/genealogist/commit/96ea20503e0541c2fa4ba15e32ed4acf005f79ab))
* apply RemoveUnusedPromotedPropertyRector ([12a4f1e](https://github.com/d9beuD/genealogist/commit/12a4f1e6a54725ed1091dc29a07db2b5bb255648))
* apply RemoveUnusedVariableAssignRector ([027324c](https://github.com/d9beuD/genealogist/commit/027324c3c23774bab7b66c2d2e9746874ef5eb3c))
* apply RemoveUnusedVariableInCatchRector ([6f8e668](https://github.com/d9beuD/genealogist/commit/6f8e668d32bf6ca79b3c26883337e39df4eec9e9))
* apply RenameForeachValueVariableToMatchMethodCallReturnTypeRector ([1c0a7a6](https://github.com/d9beuD/genealogist/commit/1c0a7a655caec7ad574f2776acc9c657bade4c21))
* apply RenameParamToMatchTypeRector ([5d773a3](https://github.com/d9beuD/genealogist/commit/5d773a3dc876eba51ff1051ee634272439759343))
* apply RenamePropertyToMatchTypeRector ([294c2cb](https://github.com/d9beuD/genealogist/commit/294c2cb2d6a867cac2d0b713b4bf84356767540c))
* apply RenameVariableToMatchMethodCallReturnTypeRector ([a345360](https://github.com/d9beuD/genealogist/commit/a345360ea4469666846f01f1ba1ef659456c445f))
* apply RenameVariableToMatchNewTypeRector ([2a7bbf1](https://github.com/d9beuD/genealogist/commit/2a7bbf153d42e5649afc73cfb4f2b372eb22d697))
* apply ReplaceMultipleBooleanNotRector ([da033a1](https://github.com/d9beuD/genealogist/commit/da033a1fc79f681704b707a0e7e99df77bb3d17b))
* apply SafeDeclareStrictTypesRector ([b73c0b7](https://github.com/d9beuD/genealogist/commit/b73c0b75c838306c7c64d87082bd18f97c8afe8b))
* apply SimplifyQuoteEscapeRector ([cc82fb7](https://github.com/d9beuD/genealogist/commit/cc82fb702e3ce3cf4ac98705b7ed232af6f6d022))
* apply SortAttributeNamedArgsRector ([36426b3](https://github.com/d9beuD/genealogist/commit/36426b3c724350b4df3fbcda0af3b5d995b5f68f))
* apply SortCallLikeNamedArgsRector ([2e48598](https://github.com/d9beuD/genealogist/commit/2e48598b231b3d4b8fa57f33c140f68df3080b6f))
* apply StrictInArrayRector ([24ffadd](https://github.com/d9beuD/genealogist/commit/24ffadd7169f419fa2f1ad4f7e0ee9e69461de9b))
* apply StringableForToStringRector ([7f1876d](https://github.com/d9beuD/genealogist/commit/7f1876dd4eddaf11064cef2ce803252b1f2c9317))
* apply StrlenZeroToIdenticalEmptyStringRector ([68f77b3](https://github.com/d9beuD/genealogist/commit/68f77b35264fe00d2ed877e8e898105bc2c78b6a))
* apply TypedPropertyFromColumnTypeRector ([bcbcb00](https://github.com/d9beuD/genealogist/commit/bcbcb001b341bebd12efe005c71a5b062d8374d8))


### Documentation

* **agents:** document Docker command and quality tooling usage ([ce14e21](https://github.com/d9beuD/genealogist/commit/ce14e219e1200a244038d28593050d2a9314e72a))


### Miscellaneous

* add PHP quality tooling ([c2b3bb1](https://github.com/d9beuD/genealogist/commit/c2b3bb16c7785a20134dc0aa7290f6e0a4719212))
* align Rector with code style ([277519c](https://github.com/d9beuD/genealogist/commit/277519cd3fd851f0333d56da93fd7c80f70b2677))
* enable Rector coding style set ([4f025e5](https://github.com/d9beuD/genealogist/commit/4f025e5f6713b0288695058bbdfaad317e7dfb29))
* enable Rector Doctrine code quality set ([ce00dd0](https://github.com/d9beuD/genealogist/commit/ce00dd010a43819e2b5fc2e12ca09d6a7ac19c63))
* enable Rector early return set ([438b24c](https://github.com/d9beuD/genealogist/commit/438b24c3c83be3f660e323c3656b32b00df751c6))
* enable Rector instanceof set ([2e8bcba](https://github.com/d9beuD/genealogist/commit/2e8bcba680c85c34a61f8fdbab09e0fd1c8e7b1c))
* enable Rector naming set ([3d3e6e5](https://github.com/d9beuD/genealogist/commit/3d3e6e5704017d7745a64ffa406a8a523fb3039b))
* enable Rector PHP 8.4 set ([549679c](https://github.com/d9beuD/genealogist/commit/549679ce0c7490d4ffcfb6ceba03fc79aa05cc2e))
* enable Rector privatization set ([94ddbdb](https://github.com/d9beuD/genealogist/commit/94ddbdbe20d0ef303cf939aae5b2f7c4186e3bd2))
* enable Rector strict booleans set ([ab6505c](https://github.com/d9beuD/genealogist/commit/ab6505cc0faadd1a881e5db960445d0b2839253d))
* enable Rector Symfony attributes set ([6ad9ba0](https://github.com/d9beuD/genealogist/commit/6ad9ba0ba42f475174b7a12a11ecbaed58926936))
* enable Rector Symfony code quality set ([009349f](https://github.com/d9beuD/genealogist/commit/009349f035ff2418714f18ae2a77676fae57a007))
* enable Rector type declaration docblocks set ([7e9c18e](https://github.com/d9beuD/genealogist/commit/7e9c18ed2db83fd2ef0d033256b54ba35b126787))
* enable Rector type declarations set ([4a71133](https://github.com/d9beuD/genealogist/commit/4a711335fb0bd3977d329d6939d216a51c608197))
* **env:** Enable MAILER_DSN in .env ([b4980cc](https://github.com/d9beuD/genealogist/commit/b4980cc04598cc10fe5dc9b58454d52e50879ba8))
* **symfony:** update route resources for Symfony 8 ([9b0cc51](https://github.com/d9beuD/genealogist/commit/9b0cc5172a23100f0f0fcaee409f7b9d90e1eadb))
* **workflow:** Add release-please workflow and config ([848226a](https://github.com/d9beuD/genealogist/commit/848226a4af027d2b4b77c8499feb728e4069503f))
