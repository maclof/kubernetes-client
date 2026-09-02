# Examples

Install a PSR-18 client and set `KUBECONFIG` before running an example:

```bash
composer require guzzlehttp/guzzle
export KUBECONFIG=/path/to/kubeconfig
php examples/discovery.php
```

| Example | Purpose |
| --- | --- |
| [`bootstrap.php`](bootstrap.php) | Creates an authenticated client from `KUBECONFIG`. |
| [`legacy-aliases.php`](legacy-aliases.php) | Existing aliases, selectors, and ConfigMap CRUD. |
| [`discovered-resources.php`](discovered-resources.php) | Automatically discovered built-ins and version overrides. |
| [`custom-resources.php`](custom-resources.php) | CRD create, get, update, patch, watch, and delete. |
| [`subresources.php`](subresources.php) | Status, logs, exec, scale, and proxy subresources. |
| [`discovery.php`](discovery.php) | Prints every API resource and supported verb advertised by the server. |

The CRUD, exec, scale, and proxy examples can change cluster state. Review the
resource names and namespace before running them.
