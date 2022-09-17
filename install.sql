ALTER TABLE poi1_poi ADD supportThreadID INT(10);
ALTER TABLE poi1_poi ADD FOREIGN KEY (supportThreadID) REFERENCES wbb1_thread (threadID) ON DELETE SET NULL;
