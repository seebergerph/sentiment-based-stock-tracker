# Stock Tracker with Twitter Analysis
This practical problem-solving project was created for the course *Cloud Computing* at the RMIT University.

## Project Description
The aim of this project was to develop a simple but informative web application that enables a user to track stocks of companies combined with the sentiment analysis of tweets about these companies, respectively. This sentiment analysis can determine the nature of the social view about the corresponding companies and whether the current global situation can influence the tracked stocks positively or negatively. The proposed web application can process tweets in real-time, which can be useful to people who want to be always updated about the performance of their stock’s portfolio depending on the news and people’s thoughts worldwide. To accomplish of this idea, we used several services from the provider Amazon Web Services (AWS) which enables the real-time processing of vast amounts of tweets while ensuring the scalability and stability of the web application.

## Architecture
![Architecture](images/architecture.png)

## Cloud Services
The entire project is built upon AWS Cloud Services and uses the following services:

 - AWS CloudFormation
 - AWS Elastic Beanstalk
 - AWS EC2
 - AWS Kinesis
 - AWS Lambda
 - AWS Comprehend
 - AWS Translate
 - AWS Glue
 - AWS Athena
 
Storage & Database:
 - AWS S3
 - AWS RDS (MySQL)

## Content
***Deployment:***
> Resources for the deployment of the entire application

***Source:***
> Source code for the twitter streaming and sentiment analysis

***Webapp:***
> Source code for the web application using **Laravel**

## References
Inspirations and code references used for this project:

***Twitter-Sentiment-Analysis:***

- [AI-Driven Social Media Dashboard](https://github.com/amazon-archives/ai-driven-social-media-dashboard)

***Financial Data API:***

- [Alpha Vantage PHP Client](https://github.com/kokspflanze/alpha-vantage-api) *(Adapted to Twelve Data)*
- [Twelve Data](https://twelvedata.com/)

***Stock Timeseries Visualization:***

- [Historical Price Charts with D3.js](https://github.com/wentjun/d3-historical-prices)
- [Relevant Tutorial](https://www.freecodecamp.org/news/how-to-build-historical-price-charts-with-d3-js-72214aaf6ba3/)
- [Responsive Chart](https://brendansudol.com/writing/responsive-d3)
