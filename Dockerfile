FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Set working directory
WORKDIR /app

# Copy existing application directory with all dependencies
COPY . .

# Set permissions for writable directory
RUN chmod -R 777 writable/

# Create history directory if not exists
RUN mkdir -p writable/history
RUN chmod -R 777 writable/history
RUN touch writable/history/data.json
RUN chmod 666 writable/history/data.json

# Make port 8001 available to the world outside this container
EXPOSE 8001

# Command to run the application
CMD ["php", "spark", "serve", "--host", "0.0.0.0", "--port", "8001"]