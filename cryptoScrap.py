import requests
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
from datetime import datetime, timezone
import time
import logging
import re
from decimal import Decimal

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Updated to comply with SQLAlchemy 2.0
Base = declarative_base()

# Database Model
class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    name_abreviation = Column(String(10), nullable=False)
    price = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))  # High precision for price
    price_change = Column(DECIMAL(20, 10), nullable=False, default=Decimal('0.0000000000'))  # High precision
    change_percent = Column(DECIMAL(10, 2), nullable=False, default=Decimal('0.00'))
    market_cap = Column(String(50), nullable=False, default="N/A")
    volume = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    circulating_supply = Column(DECIMAL(20, 2), nullable=False, default=Decimal('0.00'))
    trade_time = Column(TIMESTAMP)

# Function to connect to the database
def connect_to_database(db_url):
    try:
        engine = create_engine(db_url, pool_size=5, echo=False)
        Base.metadata.create_all(engine)
        return engine
    except Exception as e:
        logging.error(f"Error connecting to database: {e}")
        return None

# Function to scrape Yahoo Finance for given cryptocurrency symbols
def get_crypto_data(symbol, retries=3):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36"
    }
    attempt = 0
    while attempt < retries:
        try:
            logging.info(f"Fetching data for {symbol}, attempt {attempt + 1}")
            r = requests.get(url, headers=headers, timeout=5)
            if r.status_code == 200:
                soup = BeautifulSoup(r.text, 'html.parser')

                # Fetch price
                price_element = soup.find('fin-streamer', {'data-field': 'regularMarketPrice'})
                price = Decimal(price_element.text.replace(',', '').strip()) if price_element else Decimal('0.0000000000')

                # Fetch price change
                price_change_element = soup.find('fin-streamer', {'data-field': 'regularMarketChange'})
                price_change_text = price_change_element.text if price_change_element else "0.00"
                price_change_text = re.sub(r'[^\d.-]', '', price_change_text)
                price_change = Decimal(price_change_text) if price_change_text else Decimal('0.0000000000')

                # Fetch percent change
                change_percent_element = soup.find('fin-streamer', {'data-field': 'regularMarketChangePercent'})
                change_percent_text = change_percent_element.text if change_percent_element else "0.00%"
                change_percent_text = re.sub(r'[^\d.-]', '', change_percent_text)
                change_percent = Decimal(change_percent_text) if change_percent_text else Decimal('0.00')

                # Initialize market_cap, volume, circulating_supply
                market_cap = "N/A"
                volume = Decimal('0.00')
                circulating_supply = Decimal('0.00')

                # Find the table with market cap and other values
                stats_table = soup.find('section', {'data-test': 'qsp-statistics'})  # Updated selector for stats table
                if stats_table:
                    rows = stats_table.find_all('tr')
                    for row in rows:
                        header = row.find('span')
                        value = row.find('td', {'class': 'Ta(end)'})
                        if header and value:
                            header_text = header.text.strip()
                            value_text = value.text.strip()
                            if 'Market Cap' in header_text:
                                market_cap = value_text
                            elif 'Volume' in header_text and '24hr' not in header_text:
                                volume = convert_to_decimal(value_text)
                            elif 'Circulating Supply' in header_text:
                                circulating_supply = convert_to_decimal(value_text)

                # Fallback: Check the secondary data block under the main price info
                fallback_table = soup.find('div', {'data-test': 'summary-table'})
                if fallback_table:
                    rows = fallback_table.find_all('tr')
                    for row in rows:
                        header = row.find('td', {'class': 'C($primaryColor)'})
                        value = row.find('td', {'class': 'Ta(end)'})
                        if header and value:
                            header_text = header.text.strip()
                            value_text = value.text.strip()
                            if 'Market Cap' in header_text:
                                market_cap = value_text
                            elif 'Volume' in header_text and '24hr' not in header_text:
                                volume = convert_to_decimal(value_text)
                            elif 'Circulating Supply' in header_text:
                                circulating_supply = convert_to_decimal(value_text)

                return {
                    'name': symbol,
                    'name_abreviation': symbol.split('-')[0],
                    'price': price,
                    'price_change': price_change,
                    'change_percent': change_percent,
                    'market_cap': market_cap,
                    'volume': volume,
                    'circulating_supply': circulating_supply,
                    'trade_time': datetime.now(timezone.utc)
                }
            else:
                logging.error(f"Failed to fetch data for {symbol}: HTTP {r.status_code}")
                attempt += 1
                time.sleep(2)
        except Exception as e:
            logging.error(f"Error getting data for {symbol}, attempt {attempt + 1}: {e}")
            attempt += 1
            time.sleep(2)
    return None

# Function to convert text with suffixes like T/B/M into Decimal
def convert_to_decimal(value_text):
    value_text = value_text.replace(',', '').strip()
    if 'T' in value_text:
        return Decimal(value_text.replace('T', '')) * Decimal(1e12)
    elif 'B' in value_text:
        return Decimal(value_text.replace('B', '')) * Decimal(1e9)
    elif 'M' in value_text:
        return Decimal(value_text.replace('M', '')) * Decimal(1e6)
    try:
        return Decimal(value_text)
    except:
        return Decimal('0.00')

# Function to insert data into the database
def insert_data_into_database(engine, data):
    if engine is None:
        return

    Session = sessionmaker(bind=engine)
    session = Session()

    try:
        # Update existing records or insert new ones
        for item in data:
            existing = session.query(CryptoInformation).filter_by(name=item['name']).first()
            if existing:
                for key, value in item.items():
                    setattr(existing, key, value)
            else:
                session.add(CryptoInformation(**item))
        session.commit()
    except Exception as e:
        logging.error(f"Error inserting data: {e}")
        session.rollback()
    finally:
        session.close()

# Main script
if __name__ == '__main__':
    db_url = 'mysql+pymysql://user:password@localhost/crypto_express'
    engine = connect_to_database(db_url)

    if engine is None:
        exit("Failed to connect to database. Exiting.")

    # List of cryptocurrency symbols to scrape
    mycrypto = [
        'BTC-USD', 'ETH-USD', 'USDT-USD', 'SOL-USD', 'XRP-USD', 'BNB-USD', 'DOGE-USD', 'USDC-USD',
        'ADA-USD', 'SHIB-USD', 'AVAX-USD', 'TRX-USD', 'TON-USD', 'WBTC-USD', 'XLM-USD', 'DOT-USD',
        'LINK-USD', 'BCH-USD', 'SUI-USD', 'PEPE-USD', 'NEAR-USD', 'LTC-USD', 'LEO-USD', 'UNI-USD',
        'HBAR-USD', 'APT-USD', 'ICP-USD', 'DAI-USD', 'CRO-USD', 'ETC-USD', 'POL-USD', 'TAO-USD',
        'RENDER-USD', 'FET-USD', 'KAS-USD', 'FIL-USD', 'ALGO-USD', 'ARB-USD', 'VET-USD', 'STX-USD',
        'TIA-USD', 'BONK-USD', 'IMX-USD', 'ATOM-USD', 'WBT-USD', 'WIF-USD', 'OKB-USD', 'OM-USD',
        'MNT-USD', 'OP-USD'
    ]
    stockdata = []

    # Scrape data for each symbol
    for symbol in mycrypto:
        data = get_crypto_data(symbol)
        if data:
            stockdata.append(data)

    # Insert scraped data into the database
    insert_data_into_database(engine, stockdata)

    logging.info("Data scraping and insertion completed successfully.")





