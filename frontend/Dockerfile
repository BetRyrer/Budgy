# --- Image Node pour le développement du frontend (Vite dev server) ---
FROM node:20-alpine

WORKDIR /app

# On copie uniquement les manifests d'abord pour profiter du cache Docker :
# `npm install` n'est relancé que si package.json/package-lock.json changent.
COPY package*.json ./
RUN npm install

# Le reste du code est ensuite écrasé par le bind mount défini dans
# docker-compose.yml (hot-reload), cette copie sert surtout à ce que
# l'image reste utilisable seule (ex: `docker build` sans compose).
COPY . .

EXPOSE 5173

CMD ["npm", "run", "dev"]
