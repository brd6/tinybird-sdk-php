# Token API Example

Demonstrates the Token API for managing Static and JWT Tokens in Tinybird.

## Features Covered

| Method | Description |
|--------|-------------|
| `list()` | List all workspace tokens |
| `retrieve(name)` | Get token details |
| `create(params)` | Create a new static token |
| `createJwt(params)` | Create a JWT token with expiration |
| `update(name, params)` | Update token name, description, or scopes |
| `refresh(name)` | Rotate token value (invalidates old token) |
| `remove(name)` | Delete a token |

## Requirements

- **ADMIN or TOKENS scope** is required for all Token API operations

## Setup

1. Copy environment file:

```bash
cp env.example .env
```

2. Edit `.env` with your admin token:

```bash
TINYBIRD_TOKEN=p.eyJ...  # Must have ADMIN or TOKENS scope
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

### List all tokens

```php
$list = $client->tokens()->list();

foreach ($list as $token) {
    echo $token->name;
    foreach ($token->scopes as $scope) {
        echo "  - {$scope->type}";
    }
}

// Find by name
$token = $list->findByName('my_token');
```

### Get token details

```php
$token = $client->tokens()->retrieve('my_token');

echo $token->name;
echo $token->description;
echo $token->token;  // The actual token value

// Check scopes
if ($token->isAdmin()) {
    echo "This is an admin token";
}

if ($token->hasScope('DATASOURCES:READ')) {
    echo "Can read data sources";
}
```

### Create a static token

```php
use Brd6\TinybirdSdk\RequestParameters\CreateTokenParams;

$token = $client->tokens()->create(new CreateTokenParams(
    name: 'my_new_token',
    scopes: [
        'DATASOURCES:READ:my_datasource',
        'PIPES:READ:my_pipe',
    ],
    description: 'Token for my application',
));

echo $token->token;  // Save this!
```

### Create a token with row-level filter

```php
$token = $client->tokens()->create(new CreateTokenParams(
    name: 'filtered_token',
    scopes: [
        'DATASOURCES:READ:events:user_id=123',  // Only sees user 123's data
    ],
));
```

### Create a JWT token (for multi-tenant apps)

```php
use Brd6\TinybirdSdk\RequestParameters\CreateJwtTokenParams;

$token = $client->tokens()->createJwt(new CreateJwtTokenParams(
    name: 'user_jwt',
    expirationTime: time() + 3600,  // 1 hour
    scopes: [
        [
            'type' => 'PIPES:READ',
            'resource' => 'user_dashboard',
            'fixed_params' => ['user_id' => 123],  // RBAC
        ],
    ],
));
```

### Update a token

```php
use Brd6\TinybirdSdk\RequestParameters\UpdateTokenParams;

// Update description only
$token = $client->tokens()->update('my_token', new UpdateTokenParams(
    description: 'Updated description',
));

// Update scopes (replaces existing!)
$token = $client->tokens()->update('my_token', new UpdateTokenParams(
    scopes: ['ADMIN'],  // Now an admin token
));

// Rename
$token = $client->tokens()->update('old_name', new UpdateTokenParams(
    name: 'new_name',
));
```

### Refresh (rotate) a token

```php
// Use when a token is leaked or for regular rotation
$token = $client->tokens()->refresh('my_token');

echo $token->token;  // New token value - old one is now invalid!
```

### Delete a token

```php
$result = $client->tokens()->remove('my_token');

if ($result->isSuccess()) {
    echo "Token deleted";
}
```

## Scope Format

Scopes follow the format: `TYPE[:resource][:filter]`

| Scope | Description |
|-------|-------------|
| `ADMIN` | Full access |
| `TOKENS` | Manage tokens |
| `DATASOURCES:CREATE` | Create data sources |
| `DATASOURCES:READ:*` | Read all data sources |
| `DATASOURCES:READ:name` | Read specific data source |
| `DATASOURCES:READ:name:filter` | Read with row filter |
| `DATASOURCES:APPEND:name` | Append to data source |
| `PIPES:CREATE` | Create pipes |
| `PIPES:READ:*` | Read all pipes |
| `PIPES:READ:name` | Read specific pipe |

## Security Notes

- Store tokens securely (environment variables, secrets manager)
- Use minimal scopes (principle of least privilege)
- Use JWT tokens for end-user access with `fixed_params` for RBAC
- Rotate tokens regularly with `refresh()`
- Delete unused tokens

