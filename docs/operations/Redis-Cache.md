# Redis Cache

Catroweb uses Redis as the application cache backend in production. It replaces the default filesystem cache with a faster, shared in-memory store.

## What it caches

- **Symfony application cache** (`cache.app` pool): tags, extensions, flavors, achievements, feature flags, Doctrine result cache
- **Doctrine query/result caches**: ORM query plans and query results
- **Trust score calculations**: 15-minute TTL per user

## Server setup

Redis is installed on the application server and listens only on localhost.

### Install (Ubuntu 24.04)

```bash
apt-get install -y redis-server php-redis
```

### Configuration

File: `/etc/redis/redis.conf`

Key settings:

```ini
bind 127.0.0.1 -::1
maxmemory 256mb
maxmemory-policy allkeys-lru
```

- `bind 127.0.0.1` prevents external access
- `maxmemory 256mb` caps memory usage (adjust based on available server RAM)
- `allkeys-lru` evicts least-recently-used keys when memory is full

### Verify

```bash
systemctl status redis-server
redis-cli ping              # Should return PONG
redis-cli INFO memory       # Check memory usage
```

## Symfony configuration

### Environment variable

Set in `.env` (default) or server environment:

```env
REDIS_URL=redis://localhost:6379
```

### Cache adapter (prod only)

File: `config/packages/prod/doctrine.php`

```php
'cache' => [
    'app' => 'cache.adapter.redis',
    'default_redis_provider' => '%env(REDIS_URL)%',
    // ...
],
```

Dev/test environments continue using `cache.adapter.filesystem` (no Redis required locally).

## Operations

### Clear application cache

```bash
bin/console cache:pool:clear cache.app
```

### Clear all Redis data

```bash
redis-cli FLUSHDB
```

### Monitor cache hits in real-time

```bash
redis-cli MONITOR
```

### Check memory usage

```bash
redis-cli INFO memory | grep used_memory_human
```

## Troubleshooting

| Symptom                              | Cause                 | Fix                                                       |
| ------------------------------------ | --------------------- | --------------------------------------------------------- |
| `RedisException: Connection refused` | Redis not running     | `systemctl start redis-server`                            |
| Stale data after deploy              | Cache not cleared     | `bin/console cache:pool:clear cache.app`                  |
| High memory usage                    | Too many cached items | Lower `maxmemory` or reduce TTLs                          |
| PHP error: class Redis not found     | PHP extension missing | `apt-get install php-redis && systemctl restart php*-fpm` |
