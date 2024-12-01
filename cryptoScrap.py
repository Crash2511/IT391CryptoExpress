import requests
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, Column, String, DECIMAL, TIMESTAMP
from sqlalchemy.orm import declarative_base, sessionmaker
import re

# Updated to comply with SQLAlchemy 2.0
Base = declarative_base()

# Database Model
class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    name_abreviation = Column(String(10), nullable=False)
    price = Column(DECIMAL(10, 2), nullable=False, default=0.00)
    price_change = Column(DECIMAL(10, 2), nullable=False, default=0.00)
    change_percent = Column(DECIMAL(5, 2), nullable=False, default=0.00)
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
        print(f"Error connecting to database: {e}")
        return None

# Function to scrape Yahoo Finance for given cryptocurrency symbols
def get_crypto_data(symbol):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    try:
        r = requests.get(url)
        if r.status_code == 200:
            soup = BeautifulSoup(r.text, 'html.parser')
            
            # Fetch price-related fields
            price = soup.find('fin-streamer', {'data-symbol': symbol, 'data-field': 'regularMarketPrice'})
            price_change = soup.find('fin-streamer', {'data-symbol': symbol, 'data-field': 'regularMarketChange'})
            change_percent = soup.find('fin-streamer', {'data-symbol': symbol, 'data-field': 'regularMarketChangePercent'})

            # Additional fields with improved error handling
            market_cap = soup.find(string=re.compile('Market Cap'))
            market_cap = market_cap.find_next('td').text if market_cap else "N/A"

            volume = soup.find(string=re.compile('Volume'))
            volume = volume.find_next('td').text if volume else "0"

            # Validate and parse extracted data
            if price and price_change and change_percent:
                return {
                    'name': symbol,
                    'name_abreviation': symbol.split('-')[0],
                    'price': float(price.text.replace(',', '')) if price else 0.00,
                    'price_change': float(price_change.text.replace(',', '')) if price_change else 0.00,
                    'change_percent': float(change_percent.text.replace('%', '').replace(',', '')) if change_percent else 0.00,
                    'market_cap': market_cap,
                    'volume': float(volume.replace(',', '')) if volume.isnumeric() else 0.00,
                }
            else:
                print(f"Missing critical data for {symbol}")
                return None
        else:
            print(f"Failed to fetch data for {symbol}: HTTP {r.status_code}")
            return None
    except Exception as e:
        print(f"Error getting data for {symbol}: {e}")
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
        print(f"Error inserting data: {e}")
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
    mycrypto = ['BTC-USD', 'ETH-USD', 'BNB-USD', 'SOL-USD', 'DOGE-USD']
    stockdata = []

    # Scrape data for each symbol
    for symbol in mycrypto:
        data = get_crypto_data(symbol)
        if data:
            stockdata.append(data)

    # Insert scraped data into the database
    insert_data_into_database(engine, stockdata)
    print("Data extraction and insertion completed.")

