The tests can be executed only from Spryker project root folder using the following command:

```bash
vendor/bin/console transfer:generate
vendor/bin/console transfer:databuilder:generate
vendor/bin/codecept build -c vendor/spryker-eco/algolia
vendor/bin/codecept run -c vendor/spryker-eco/algolia
```
