# Environment Variables API Example

Demonstrates the Environment Variables API for managing secrets and configuration in Tinybird.

## Features Covered

| Method | Description |
|--------|-------------|
| `list()` | List all variables (values hidden) |
| `retrieve(name)` | Get variable details (value hidden) |
| `create(name, value, type?)` | Create a new variable |
| `update(name, value)` | Update variable value |
| `remove(name)` | Delete a variable |

## Requirements

- **Workspace admin token** is required for all Variables API operations

## Setup

1. Copy environment file:

```bash
cp env.example .env
```

2. Edit `.env` with your **admin** token:

```bash
TINYBIRD_TOKEN=p.eyJ...  # Must be admin token
TINYBIRD_LOCAL=false
```

3. Install dependencies:

```bash
composer install
```

4. Run the example:

```bash
php index.php
```

## Usage Examples

### List all variables

```php
$list = $client->variables()->list();

foreach ($list as $var) {
    echo $var->name;
    echo $var->type->value;  // 'secret'
}

// Or access as array
$vars = $list->getVariables();
```

### Get variable details

```php
$var = $client->variables()->retrieve('my_variable');

echo $var->name;
echo $var->type->value;
echo $var->createdAt->format('Y-m-d');
// Note: value is NOT returned for security
```

### Create a variable

```php
use Brd6\TinybirdSdk\Enum\VariableType;

$var = $client->variables()->create(
    'db_password',
    'my_secret_value',
    VariableType::SECRET,  // optional, default
);
```

### Update a variable

```php
$var = $client->variables()->update('db_password', 'new_secret_value');
```

### Delete a variable

```php
$result = $client->variables()->remove('db_password');

if ($result->isSuccess()) {
    echo "Deleted!";
}
```

## Using Variables in Pipes

After creating variables, use them in your Pipes with `tb_secret()`:

```sql
%
SELECT *
FROM postgresql(
    'host:port',
    'database',
    'table',
    'user',
    {{tb_secret('db_password')}}
)
```

## Limits

- 5 requests per second
- 100 variables per Workspace
- 8 KB max value size

## Security Notes

- Values are encrypted at rest
- Values are never returned via API (only name, type, timestamps)
- Variables with `type=secret` cannot be exposed in API Endpoint SELECT clauses

