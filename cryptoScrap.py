import requests
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
import re
from datetime import datetime, timezone
import time
import logging

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Updated to comply with SQLAlchemy 2.0
Base = declarative_base()

# Database Model
class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    name_abreviation = Column(String(10), nullable=False)
    price = Column(DECIMAL(10, 2), nullable=False, default=0.00)
    market_cap = Column(String(20), nullable=False, default="0")
    volume = Column(DECIMAL(20, 2), nullable=False, default=0.00)
    circulating_supply = Column(DECIMAL(20, 2), nullable=False, default=0.00)
    total_supply = Column(DECIMAL(20, 2), nullable=False, default=0.00)
    price_high = Column(DECIMAL(10, 2), nullable=False, default=0.00)
    price_low = Column(DECIMAL(10, 2), nullable=False, default=0.00)
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
# Added retry mechanism and increased timeout
def get_crypto_data(symbol, retries=3):
    import requests
    url = f'https://finance.yahoo.com/quote/{symbol}'
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36"
    }
    attempt = 0
    while attempt < retries:
        try:
            logging.info(f"Fetching data for {symbol}, attempt {attempt + 1}")
            r = requests.get(url, headers=headers, timeout=10)
            if r.status_code == 200:
                soup = BeautifulSoup(r.text, 'html.parser')
                
                # Fetch price-related fields using updated CSS selectors
                price_element = soup.find('fin-streamer', {'data-field': 'regularMarketPrice'})
                price = float(price_element.text.replace(',', '')) if price_element else 0.00

                # Additional fields with improved error handling and more robust parsing
                market_cap = "N/A"
                volume = "0"

                # Try different ways to find market cap and volume
                try:
                    market_cap_element = soup.find('td', string=re.compile(r'Market Cap', re.IGNORECASE))
                    if market_cap_element:
                        market_cap = market_cap_element.find_next('td').text.strip()
                except Exception as e:
                    logging.warning(f"Error finding market cap for {symbol}: {e}")

                try:
                    volume_element = soup.find('td', string=re.compile(r'Volume', re.IGNORECASE))
                    if volume_element:
                        volume = volume_element.find_next('td').text.strip()
                except Exception as e:
                    logging.warning(f"Error finding volume for {symbol}: {e}")

                # Validate and parse extracted data
                return {
                    'name': symbol,
                    'name_abreviation': symbol.split('-')[0],
                    'price': price,
                    'market_cap': market_cap,
                    'volume': float(re.sub('[^0-9.]', '', volume)) if volume else 0.00,
                    'trade_time': datetime.now(timezone.utc)
                }
            else:
                logging.error(f"Failed to fetch data for {symbol}: HTTP {r.status_code}")
                attempt += 1
                time.sleep(2)
        except requests.exceptions.RequestException as e:
            logging.error(f"Network error getting data for {symbol}, attempt {attempt + 1}: {e}")
            attempt += 1
            time.sleep(2)
        except Exception as e:
            logging.error(f"Error getting data for {symbol}, attempt {attempt + 1}: {e}")
            attempt += 1
            time.sleep(2)
    return None

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
    db_url = 'mysql+pymysql://user:IT391!@localhost/crypto_express'
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

    # Scrape data for each symbol with delay to avoid rate limiting
    for symbol in mycrypto:
        data = get_crypto_data(symbol)
        if data:
            stockdata.append(data)
        time.sleep(2)  # Add delay to avoid being blocked

    # Insert scraped data into the database
    insert_data_into_database(engine, stockdata)
    logging.info("Data extraction and insertion completed.")
