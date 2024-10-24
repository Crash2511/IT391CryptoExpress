from bs4 import BeautifulSoup
import requests

url = ''
page = requests.get(url)
soup = BeautifulSoup(page.text, 'html')

print(soup)

soup.find('table')

soup.find_all('table')[1]

soup.find('table', class_ = 'our cryptos')

table = soup.find_all('table)[1]
print(table)

world_titles = table.find_all('th')

world_table_titles = [title.text.strip() for title in world_titles]

import pandas as pd

df = pd.DataFrame(colimns = world_table_titles)
column_data = table.find_all('tr')
for row in column_data[1:]:
  row_data = row.find_all('td')
  individual_row_data = [data.text.strip() for data in row_data]
  length = len(df)
  df.loc[length] = individual_row_data

df.to_csv('pathway idk where', index = False)

