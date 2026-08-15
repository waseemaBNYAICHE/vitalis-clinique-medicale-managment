# syntax=docker/dockerfile:1

# Node 22 LTS - matches the toolchain Vite 8 targets.
FROM node:22-alpine

ENV NODE_ENV=development \
    npm_config_update_notifier=false \
    npm_config_fund=false \
    npm_config_audit=false

WORKDIR /app

# Seeds the node_modules volume on first boot and caches the layer afterwards.
COPY package.json package-lock.json* ./
RUN npm install --no-audit --no-fund

COPY . .

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint

EXPOSE 5173

ENTRYPOINT ["entrypoint"]
CMD ["npm", "run", "dev"]
