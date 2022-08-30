<h3 align="center">Aspire API System for Loan management</h3>
<p>Created By: <b>Abzal</b></p>
<h3>List of APIs</h3>
<ul>
<li>Create Customer</li>
<li>Get API Key</li>
<li>Create Loan</li>
<li>Approve Loan</li>
<li>View Loan</li>
<li>Do Repayment</li>
</ul>
<h3>Overview</h3>
<p>In this project, I've created API system for loan creation by customer and repayments using Laravel Framework.</p>
<h4>Setup</h4>
<ul>
<li>Take a pull to your local</li>
<li>Create a MySQL database</li>
<li>Update the database connections in `.env` file</li>
<li>Open terminal and go to project directory</li>
<li>Run the following command for Database migrations</li>
```
php artisan migrate
```
<li>Run the following command for Database seeding</li>
```
php artisan db:seed
```
<li>Run the following command</li>
```
php artisan serve
```
</ul>
<p>Attached postman collection for running the project</p>
<h3>Note</h3>
<p>Never truncate user table. If truncated, run the seeder again</p>
<h3>Implementation Overview</h3>
Once all setup done, the DB will have 50 records of customers and 1 record of user. Here, Customer data will be used for all API's except Loan approval API.
For Loan approval API, User data is used.
In the list of 6 API's first 2 (Create Customer and Get API Key) API's are optional since, the customer data will be created in the DB seeder. If you want to create new customer, you can use this 2 APIs.
I've created a middleware to validate the request and authenticate it with customer's email and api_key. API key is the 32 digit alpha numeric string.
When the API request is made, the request will be landed to middleware where it will validate the request with email and api_key for all APIs except approve loan. 
Approve loan API can be called by internal user where this user data will be available in User table. After running the seeder, the User record will be created. User's email is `admin@test.com` and the password is `aspire@123`.
<h3>Flow</h3>
<ul>
<li>Customer calls create customer API with email ID and name.</li>
<img width="1368" alt="image" src="https://user-images.githubusercontent.com/16385639/187489281-42629c2c-7108-4391-80f0-a74a200e9e19.png">
<li>After the successfull creation of customer, Get API Key API is called with email.</li>
<img width="1368" alt="image" src="https://user-images.githubusercontent.com/16385639/187490438-05de1a3a-a772-44d9-aae4-8d17a6ebd6a1.png">
<li>Now we have email and API key. Next, call Create Loan API with email, api_key, amount_required & loan_term.</li>
<li>System will create a new loan and send the loan_id as response. Now, the loan state will be PENDING.</li>
<img width="1368" alt="image" src="https://user-images.githubusercontent.com/16385639/187490702-4e3771d5-8e79-4074-8176-2bd627950fe9.png">
<li>Internal User / Admin User will approve this Loan by calling Approve Loan API with email, password and loan_id. Now the loan state becomes APPROVED.</li>
<li>When the Loan is approved, system will create records in repayments table. Lets assume the loan amount is 10000 and the loan term is 4 months, system will create 4 records in repayments table with each record's due amount as 2500. All this repayment records state will be PENDING.</li>
<img width="1368" alt="image" src="https://user-images.githubusercontent.com/16385639/187490849-9c2906ed-725d-4e4a-94a1-f6995bce9662.png">
<li>Customer can view the list of loans created by him/her. Detailed list will load with following fields.</li>
<img width="424" alt="image" src="https://user-images.githubusercontent.com/16385639/187489105-1c69bbf4-980b-4e18-8f0c-3fec648c9c69.png">
<li>Customer does the repayment. Consider the above said example of 10000 loan amount with loan term as 4. In this case, the repayment amount is 2500 x 4 weeks. Now the customer sends amount as 1500 which is lessthan due amount. So system will throw error like below.</li>
<img width="1368" alt="image" src="https://user-images.githubusercontent.com/16385639/187493449-84499d41-f4da-4474-903c-9c7bc79659ff.png">
<li>Customer makes exact amount as repayment. Then the system will accept the repayment and update that repayment record as PAID</li>
<img width="1368" alt="image" src="https://user-images.githubusercontent.com/16385639/187493974-89423f3b-74fb-484b-bf40-92eec19f28a0.png">
<li>Now, the view loan API will show the following response. Note the first repayment record status updated as PAID</li>
<img width="1359" alt="image" src="https://user-images.githubusercontent.com/16385639/187494684-1977b530-8354-444d-8ffb-2af40f2d737a.png">
<li>Customer makes repayment amount greater than due amount for example the amount customer pays is 4000 instead of 2500. Since customer made 1st repayment 2500 out of 10000. Now the balance amount is 7500. In which the customer tries to make payment as 4000. So 2nd repayment will be updated with PAID and remaining 3 and 4 repayments will be adjusted with 1750 each. So at any cost, customer cannot make payment morethan 10000.</li>
<img width="1359" alt="image" src="https://user-images.githubusercontent.com/16385639/187495675-34ca7779-a344-4a03-8327-ac0edce299f6.png">
<img width="1359" alt="image" src="https://user-images.githubusercontent.com/16385639/187495760-604b002f-aa56-49b4-a561-8117f53c72b2.png">
<li>Now customer makes another repayment as 1750. And for the final repayment of 4th repayment, the loan state also will be changed as PAID.</li>
<img width="1359" alt="image" src="https://user-images.githubusercontent.com/16385639/187501958-98a28ac4-ade2-4fa7-a77a-8da410a41383.png">
</ul>
<p>Unit testing covered with the Feature testing. Please run `vendor/bin/phpunit` to run the testing part.</p>
<p>This is the overall flow of the project. Please reach me over my email if anything blocker.</p>
