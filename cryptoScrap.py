import logging
from selenium import webdriver
from selenium.webdriver.chrome.service import Service as ChromeService
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException
from concurrent.futures import ThreadPoolExecutor
from decimal import Decimal
from datetime import datetime, timezone
from sqlalchemy import create_engine, Table, Column, String, MetaData, Float, DateTime
import pymysql

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Database connection URL
db_url = 'mysql+pymysql://user:password@localhost/crypto_express'
engine = create_engine(db_url)
metadata = MetaData()

# Define a table to store cryptocurrency data
crypto_table = Table('crypto_data', metadata,
    Column('name', String(50)),
    Column('price', Float),
    Column('price_change', Float),
    Column('change_percent', Float),
    Column('previous_close', String(50)),
    Column('open', String(50)),
    Column('price_low', Float),
    Column('price_high', Float),
    Column('market_cap', String(50)),
    Column('circulating_supply', Float),
    Column('volume', Float),
    Column('trade_time', DateTime)
)
metadata.create_all(engine)

def scrape_crypto(driver, symbol):
    """Scrape cryptocurrency data from Yahoo Finance."""
    url = f'https://finance.yahoo.com/quote/{symbol}'
    logging.info(f"Scraping data for {symbol}...")
    crypto_data = {
        'name': symbol,
        'price': None,
        'price_change': None,
        'change_percent': None,
        'previous_close': None,
        'open': None,
        'price_low': None,
        'price_high': None,
        'market_cap': None,
        'circulating_supply': None,
        'volume': None,
        'trade_time': datetime.now(timezone.utc)
    }

    try:
        driver.get(url)
        WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketPrice"]'))
        )

        # Scrape price-related data
        crypto_data['price'] = Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketPrice"]').text.replace(',', ''))
        crypto_data['price_change'] = Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketChange"]').text.replace(',', ''))
        crypto_data['change_percent'] = Decimal(driver.find_element(By.CSS_SELECTOR, 'fin-streamer[data-field="regularMarketChangePercent"]').text.strip('%'))

        # Scrape summary table
        rows = driver.find_elements(By.CSS_SELECTOR, 'div#quote-summary table tbody tr')
        for row in rows:
            header = row.find_element(By.CSS_SELECTOR, 'td:first-child').text.strip()
            value = row.find_element(By.CSS_SELECTOR, 'td:last-child').text.strip()

            if 'Previous Close' in header:
                crypto_data['previous_close'] = value
            elif 'Open' in header:
                crypto_data['open'] = value
            elif "Day's Range" in header:
                low, high = value.split(' - ')
                crypto_data['price_low'] = Decimal(low.strip().replace(',', ''))
                crypto_data['price_high'] = Decimal(high.strip().replace(',', ''))
            elif 'Market Cap' in header:
                crypto_data['market_cap'] = value
            elif 'Circulating Supply' in header:
                crypto_data['circulating_supply'] = Decimal(value.replace(',', '').strip())
            elif 'Volume' in header:
                crypto_data['volume'] = Decimal(value.replace(',', '').strip())

    except (TimeoutException, WebDriverException) as e:
        logging.warning(f"Error fetching data for {symbol}: {e}")
    return crypto_data

# Function to set up a Selenium WebDriver
def create_driver():
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    service = ChromeService('/usr/bin/chromedriver')  # Update the path to chromedriver
    driver = webdriver.Chrome(service=service, options=options)
    return driver

# Function to scrape all cryptocurrencies using threading
def scrape_all_cryptos(crypto_list):
    results = []
    with ThreadPoolExecutor(max_workers=5) as executor:  # Adjust max_workers based on system resources
        # Create a pool of drivers
        drivers = [create_driver() for _ in range(5)]
        futures = []
        for i, symbol in enumerate(crypto_list):
            futures.append(executor.submit(scrape_crypto, drivers[i % len(drivers)], symbol))

        # Collect results
        for future in futures:
            results.append(future.result())

        # Close all drivers
        for driver in drivers:
            driver.quit()
    return results

# Function to insert scraped data into the database
def insert_into_db(scraped_data):
    with engine.connect() as connection:
        for data in scraped_data:
            insert_stmt = crypto_table.insert().values(
                name=data['name'],
                price=data['price'],
                price_change=data['price_change'],
                change_percent=data['change_percent'],
                previous_close=data['previous_close'],
                open=data['open'],
                price_low=data['price_low'],
                price_high=data['price_high'],
                market_cap=data['market_cap'],
                circulating_supply=data['circulating_supply'],
                volume=data['volume'],
                trade_time=data['trade_time']
            )
            connection.execute(insert_stmt)

if __name__ == '__main__':
    # List of cryptocurrencies to scrape
    mycrypto = [
        'BTC-USD', 'ETH-USD', 'SOL-USD', 'XRP-USD', 'ADA-USD', 'DOGE-USD', 'BNB-USD'
    ]

    # Scrape all cryptocurrencies
    scraped_data = scrape_all_cryptos(mycrypto)

    # Insert the scraped data into the database
    insert_into_db(scraped_data)

    # Output results
    for data in scraped_data:
        logging.info(data)










