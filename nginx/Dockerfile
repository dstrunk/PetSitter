# Dockerfile
FROM nginx:alpine

# Remove the default nginx.conf
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom configuration
COPY conf.d/app.conf /etc/nginx/conf.d/

# Note: This will require app.conf in the nginx/conf.d directory
