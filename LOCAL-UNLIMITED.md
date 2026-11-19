# Local unlimited mode

Akaunting ships two paywalls that both phone home to `https://api.akaunting.com`. On a self-hosted
instance **without an API key the app is not merely "free tier" — it refuses to create users, companies
or invoices at all**, because the limit lookup returns `false` and every `authorize()` throws
`NotAllowed`.

Verified upstream behaviour on this install (`AKAUNTING_LOCAL_UNLIMITED=false`):

```
BLOCKED upstream-style: App\Exceptions\Job\NotAllowed: Not able to create a new user.
```

## Where the gates live

| Gate | File | Effect |
|---|---|---|
| Plan limits | `app/Traits/Plans.php::getPlanLimitByType()` | returns `false` limits when `plans/limits` API fails → all creates blocked |
| Create guards | `app/Jobs/Auth/CreateUser.php:96`, `app/Jobs/Common/CreateCompany.php:60`, `app/Jobs/Document/CreateDocument.php:69` | `authorize()` throws `NotAllowed` |
| Create redirect | `app/Http/Middleware/RedirectIfHitPlanLimits.php` | `GET .../create` bounces to index |
| Upsell banner | `app/Http/ViewComposers/PlanLimits.php` | "action required" alert on users/companies/invoices |
| Module subscriptions | `app/Traits/Modules.php::loadSubscriptions()` + `getModulesLimitOfSubscription()` | disables/uninstalls modules the API says are lapsed |

## The patch (3 files, one switch)

`config/app.php`

```php
'local_unlimited' => (bool) env('AKAUNTING_LOCAL_UNLIMITED', true),
```

`app/Traits/Plans.php` — short-circuit before the API call:

```php
public function getPlanLimitByType($type): object
{
    if (config('app.local_unlimited', true)) {
        return $this->unlimitedLimit();   // limit -1, used 0, action/view true
    }
    ...
}
```

`app/Traits/Modules.php` — no subscription fetch, nothing to enforce:

```php
public function loadSubscriptions()
{
    if (config('app.local_unlimited', true)) {
        return [];
    }
    ...
}
```

Set `AKAUNTING_LOCAL_UNLIMITED=false` to get stock upstream behaviour back. No other limits exist:
the remaining `NotAllowed` throws are business rules (last user, non-empty company, posted document).

## Verification (live instance, 2026-11-19)

```
CreateCompany::authorize OK
CreateUser::authorize OK
CreateDocument::authorize OK
```

with `getAnyActionLimitOfPlan()` → `{"action_status":true,"view_status":true,"message":"Success"}`.

## Applying

`Dockerfile.local` overlays the three files on the official image:

```bash
docker build -f Dockerfile.local -t akaunting-local:latest .
```

The stack at `~/.dockhand/stacks/Production/akaunting/compose.yaml` builds from this repo
(context `~/repos/akaunting`), so `docker compose up -d --build` is the update path.
