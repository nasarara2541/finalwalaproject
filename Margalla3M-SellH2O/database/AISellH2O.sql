CREATE DATABASE AISellH2O;
GO
USE AISellH2O;
GO

CREATE TABLE Share_Inventory (
    INV_No              INT             NOT NULL IDENTITY(1,1),
    INV_Name            VARCHAR(100)    NULL,
    INV_Desc            VARCHAR(255)    NULL,
    Initial_Cost        DECIMAL(12,2)   NULL,
    INV_Type            VARCHAR(50)     NULL,
    ACC_NO              VARCHAR(20)     NULL,
    CONSTRAINT PK_Share_Inventory PRIMARY KEY (INV_No)
);
GO

CREATE TABLE Share_Calculation (
    Share_Year          INT             NOT NULL,
    Share_Month         VARCHAR(20)     NOT NULL,
    INV_no              INT             NOT NULL,
    Amount              DECIMAL(12,2)   NULL,
    Filing_Date         DATE            NULL,
    User_id             VARCHAR(50)     NULL,
    CONSTRAINT PK_Share_Calculation PRIMARY KEY (Share_Year, Share_Month, INV_no),
    CONSTRAINT FK_ShareCalc_Inventory FOREIGN KEY (INV_no)
        REFERENCES Share_Inventory(INV_No)
);
GO

CREATE TABLE Share_Holders (
    ShareHolder_No      INT             NOT NULL IDENTITY(1,1),
    ShareHolder_Name    VARCHAR(100)    NOT NULL,
    Address             VARCHAR(255)    NULL,
    City                VARCHAR(100)    NULL,
    Tel_No              VARCHAR(20)     NULL,
    Mobile_No           VARCHAR(20)     NULL,
    Email               VARCHAR(100)    NULL,
    Invested_Amount     DECIMAL(12,2)   NULL,
    Status              VARCHAR(20)     NULL,
    CONSTRAINT PK_Share_Holders PRIMARY KEY (ShareHolder_No)
);
GO

CREATE TABLE ShareHolder_Investment (
    Share_Year          INT             NOT NULL,
    Share_Month         VARCHAR(20)     NOT NULL,
    ShareHolder_No      INT             NOT NULL,
    Share_Amount        DECIMAL(12,2)   NULL,
    Share_NewAmount     DECIMAL(12,2)   NULL,
    Share_Percent       DECIMAL(5,2)    NULL,
    Sharable_NetIncome  DECIMAL(12,2)   NULL,
    ShareRec_Amount     DECIMAL(12,2)   NULL,
    Invest_Date         DATE            NULL,
    CONSTRAINT PK_ShareHolder_Investment PRIMARY KEY (Share_Year, Share_Month, ShareHolder_No),
    CONSTRAINT FK_ShareHolderInv_Holder FOREIGN KEY (ShareHolder_No)
        REFERENCES Share_Holders(ShareHolder_No)
);
GO

CREATE TABLE Manufacture (
    Manufacture_no      INT             NOT NULL IDENTITY(1,1),
    M_Name              VARCHAR(100)    NOT NULL,
    M_ShortName         VARCHAR(50)     NULL,
    Address             VARCHAR(255)    NULL,
    City                VARCHAR(100)    NULL,
    Tel_no              VARCHAR(20)     NULL,
    CONSTRAINT PK_Manufacture PRIMARY KEY (Manufacture_no)
);
GO

CREATE TABLE ST_Supplier (
    SUPPLIER_CODE       VARCHAR(20)     NOT NULL,
    SUPPLIER_NAME       VARCHAR(100)    NOT NULL,
    CONTACT_PERSON      VARCHAR(100)    NULL,
    ADDRESS             VARCHAR(255)    NULL,
    CITY                VARCHAR(100)    NULL,
    REGION              VARCHAR(100)    NULL,
    POSTAL_CODE         VARCHAR(20)     NULL,
    TELEPHONE_NO        VARCHAR(20)     NULL,
    MOBILE_NO           VARCHAR(20)     NULL,
    FAX_NO              VARCHAR(20)     NULL,
    EMAIL               VARCHAR(100)    NULL,
    CONSTRAINT PK_ST_Supplier PRIMARY KEY (SUPPLIER_CODE)
);
GO

CREATE TABLE Item_Booking (
    ID                  INT             NOT NULL IDENTITY(1,1),
    Item_name           VARCHAR(100)    NULL,
    Demand_qty          INT             NULL,
    Booking_date        DATE            NULL,
    Demand_date         DATE            NULL,
    Supplier_code       VARCHAR(20)     NULL,
    Comments            VARCHAR(255)    NULL,
    Status              VARCHAR(20)     NULL,
    Prod_Type           VARCHAR(50)     NULL,
    CONSTRAINT PK_Item_Booking PRIMARY KEY (ID),
    CONSTRAINT FK_ItemBooking_Supplier FOREIGN KEY (Supplier_code)
        REFERENCES ST_Supplier(SUPPLIER_CODE)
);
GO

CREATE TABLE ST_STOCKRECEIPT (
    Invoice_no          INT             NOT NULL IDENTITY(1,1),
    INVOICE_DATE        DATETIME        NULL,
    SUPPLIER_CODE       VARCHAR(20)     NULL,
    RECEIVED_DATE       DATETIME        NULL,
    TOTAL_AMOUNT        DECIMAL(12,2)   NULL,
    DISCOUNT            DECIMAL(10,2)   NULL,
    STATUS              VARCHAR(20)     NULL,
    RECEIVED_BY         VARCHAR(100)    NULL,
    User_id             VARCHAR(50)     NULL,
    CONSTRAINT PK_ST_STOCKRECEIPT PRIMARY KEY (Invoice_no),
    CONSTRAINT FK_StockReceipt_Supplier FOREIGN KEY (SUPPLIER_CODE)
        REFERENCES ST_Supplier(SUPPLIER_CODE)
);
GO

CREATE TABLE Item_Stock (
    STOCK_NUMBER        VARCHAR(20)     NOT NULL,
    BRAND_NAME          VARCHAR(100)    NULL,
    ITEM_NAME           VARCHAR(100)    NULL,
    ITEM_TYPE           VARCHAR(50)     NULL,
    STOCK_TYPE          VARCHAR(50)     NULL,
    VOLUME_ML           VARCHAR(20)     NULL,
    AVAILABLE_STATUS    VARCHAR(20)     NULL,
    SIZE_DESC           VARCHAR(100)    NULL,
    BARCODE             VARCHAR(50)     NULL,
    OTC_QTY             INT             NULL,
    UNIT_TYPE           VARCHAR(20)     NULL,
    UNITS_PERITEM       INT             NULL,
    PRICE               DECIMAL(10,2)   NULL,
    QTY_INHAND          INT             NULL,
    MANUFACTURE_NO      INT             NULL,
    LOCATION            VARCHAR(100)    NULL,
    PERCENTAGE_DISC     DECIMAL(5,2)    NULL,
    SUPPLIERS_LIST      VARCHAR(255)    NULL,
    CONSTRAINT PK_Item_Stock PRIMARY KEY (STOCK_NUMBER),
    CONSTRAINT FK_ItemStock_Manufacture FOREIGN KEY (MANUFACTURE_NO)
        REFERENCES Manufacture(Manufacture_no)
);
GO

CREATE TABLE ST_STOCKRECEIPTDETAIL (
    Invoice_no          INT             NOT NULL,
    STOCK_NUMBER        VARCHAR(20)     NOT NULL,
    PRICE_PERITEM       DECIMAL(10,2)   NULL,
    ITEMS_RECEIVED      INT             NULL,
    ITEMS_AVAILABLE     INT             NULL,
    EXPIRY_DATE         DATE            NULL,
    BATCH_NO            VARCHAR(50)     NULL,
    UNITS_PERITEM       INT             NULL,
    WPRICE_PERITEM      DECIMAL(10,2)   NULL,
    PPRICE_PERITEM      DECIMAL(10,2)   NULL,
    Update_Status       VARCHAR(20)     NULL,
    Price_PerUnit       DECIMAL(10,2)   NULL,
    PPrice_PerUnit      DECIMAL(10,2)   NULL,
    Record_date         DATE            NULL,
    Tax_Percentage      DECIMAL(5,2)    NULL,
    Tax_amount          DECIMAL(10,2)   NULL,
    Serial_No           INT             NULL,
    CONSTRAINT PK_ST_STOCKRECEIPTDETAIL PRIMARY KEY (Invoice_no, STOCK_NUMBER),
    CONSTRAINT FK_StockReceiptDetail_Receipt FOREIGN KEY (Invoice_no)
        REFERENCES ST_STOCKRECEIPT(Invoice_no),
    CONSTRAINT FK_StockReceiptDetail_Stock FOREIGN KEY (STOCK_NUMBER)
        REFERENCES Item_Stock(STOCK_NUMBER)
);
GO

-- brackets are added Transaction is already a (reserved word)
CREATE TABLE [Transaction] (
    Trans_no            INT             NOT NULL IDENTITY(1,1),
    Cust_name           VARCHAR(100)    NULL,
    Cust_telno          VARCHAR(20)     NULL,
    Trans_date          DATETIME        NULL,
    Trans_type          VARCHAR(20)     NULL,
    Trans_amount        DECIMAL(12,2)   NULL,
    Disc_percentage     DECIMAL(5,2)    NULL,
    Tax_status          VARCHAR(20)     NULL,
    Branch_code         VARCHAR(20)     NULL,
    Gross_amount        DECIMAL(12,2)   NULL,
    Paid_amount         DECIMAL(12,2)   NULL,
    Balance_amount      DECIMAL(12,2)   NULL,
    User_id             VARCHAR(50)     NULL,
    CONSTRAINT PK_Transaction PRIMARY KEY (Trans_no)
);
GO

CREATE TABLE trans_detail (
    Trans_no            INT             NOT NULL,
    stock_number        VARCHAR(20)     NOT NULL,
    quantity            INT             NULL,
    Price_PerItem       DECIMAL(10,2)   NULL,
    amount              DECIMAL(12,2)   NULL,
    User_id             VARCHAR(50)     NULL,
    Status              VARCHAR(20)     NULL,
    Invoice_No          INT             NULL,
    PPrice_amount       DECIMAL(10,2)   NULL,
    CONSTRAINT PK_trans_detail PRIMARY KEY (Trans_no, stock_number),
    CONSTRAINT FK_transdetail_Transaction FOREIGN KEY (Trans_no)
        REFERENCES [Transaction](Trans_no),
    CONSTRAINT FK_transdetail_Stock FOREIGN KEY (stock_number)
        REFERENCES Item_Stock(STOCK_NUMBER)
);
GO

SELECT * FROM Manufacture;