FROM nginx:1.25-alpine AS base

# ─── Builder для фронтенда ────────────────────────────────────────────────────
FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --prefer-offline

COPY frontend/ .
RUN npm run build

# ─── Production nginx ─────────────────────────────────────────────────────────
FROM base AS prod

COPY docker/nginx/nginx.conf        /etc/nginx/nginx.conf
COPY docker/nginx/conf.d/app.conf   /etc/nginx/conf.d/default.conf

# Статика фронтенда
COPY --from=frontend-builder /app/dist /var/www/html

EXPOSE 80 443