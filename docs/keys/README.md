# OAuth2 keys

Здесь должны лежать RSA-ключи для подписи (private) и проверки (public) токенов.

**Ключи в репозиторий НЕ коммитятся** (см. `.gitignore` пакета) — они уникальны для каждой
установки и являются секретом. Сгенерируйте свою пару:

```bash
bash ../generate-keys.sh
# либо вручную:
openssl genrsa -out private.key 2048
openssl rsa -in private.key -pubout -out public.key
chmod 600 private.key public.key
```

Путь к ключам указывается в конфиге приложения (composition root), например в
`app/rest/config/main.php` у компонентов `oauth2AuthServer.privateKeyPath` и
`oauth2ResourceServer.publicKeyPath`. В проде — вынести в Docker Secrets (см. TODO в конфиге rest).
