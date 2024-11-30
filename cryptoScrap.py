import requests
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, Column, Integer, String, Float, DECIMAL
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
import re

Base = declarative_base()

class CryptoInformation(Base):
    __tablename__ = 'crypto_information'
    name = Column(String(50), primary_key=True, nullable=False)
    name_abreviation = Column(String(10), nullable=False)
    price = Column(DECIMAL(10, 2), nullable=False, default=0.0)
    price_change = Column(DECIMAL(10, 2), nullable=False, default=0.0)
    change_percent = Column(DECIMAL(5, 2), nullable=False, default=0.0)
    market_cap = Column(String(20), nullable=False, default='0')
    volume = Column(DECIMAL(15, 2), nullable=False, default=0.0)
    circulating_supply = Column(DECIMAL(20, 2), nullable=False, default=0.0)
    total_supply = Column(DECIMAL(20, 2), nullable=False, default=0.0)
    price_high = Column(DECIMAL(10, 2), nullable=False, default=0.0)
    price_low = Column(DECIMAL(10, 2), nullable=False, default=0.0)
    trade_time = Column(String(50))


# Function to connect to the database
def connect_to_database(db_url):
    try:
        engine = create_engine(db_url, pool_size=5, echo=True)
        Base.metadata.create_all(engine)
        return engine
    except Exception as e:
        print(f"Error connecting to database: {e}")
        return None


# Function to scrape Yahoo finance for given cryptocurrency symbols
def get_crypto_data(symbol):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    try:
        r = requests.get(url)
        if r.status_code == 200:
            soup = BeautifulSoup(r.text, 'html.parser')
            
            price_element = soup.find('fin-streamer', {'data-symbol': symbol, 'data-field': 'regularMarketPrice'})
            change_element = soup.find('fin-streamer', {'data-symbol': symbol, 'data-field': 'regularMarketChange'})
            change_percent_element = soup.find('fin-streamer', {'data-symbol': symbol, 'data-field': 'regularMarketChangePercent'})
            market_cap_element = soup.find(text=re.compile('Market Cap')).find_next('td').text
            volume_element = soup.find(text=re.compile('Volume')).find_next('td').text
            
            if price_element and change_element and change_percent_element:
                price = price_element.text.strip()
                change = change_element.text.strip()
                change_percent = change_percent_element.text.strip()
                return {
                    'name': symbol,
                    'name_abreviation': symbol.split('-')[0],
                    'price': float(price.replace(',', '')),
                    'price_change': float(change.replace(',', '')),
                    'change_percent': float(change_percent.replace('%', '').replace(',', '')),
                    'market_cap': market_cap_element,
                    'volume': volume_element
                }
            else:
                print(f"Required data not found for {symbol}")
                return None
        else:
            print(f"Failed to fetch data for {symbol}: {r.status_code}")
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
        session.bulk_insert_mappings(CryptoInformation, data)
        session.commit()
    except Exception as e:
        print(f"Error inserting data: {e}")
        session.rollback()
    finally:
        session.close()


if __name__ == '__main__':
    db_url = 'mysql+mysqlconnector://user:password@localhost/crypto_simulator'

    engine = connect_to_database(db_url)
    if engine is None:
        exit("Failed to connect to database. Exiting.")

    mycrypto = ['BTC-USD', 'ETH-USD', 'BNB-USD', 'SOL-USD', 'DOGE-USD']
    stockdata = []

    for symbol in mycrypto:
        data = get_crypto_data(symbol)
        if data:
            stockdata.append(data)

    insert_data_into_database(engine, stockdata)

    print("Data extraction and insertion completed.")
