# Garante que as pastas internas do storage existam
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             bootstrap/cache

# Dá permissão para o usuário que roda o servidor (geralmente www-data)
RUN chmod -R 775 storage bootstrap/cache
