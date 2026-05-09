"use client";
import React, { useState } from "react";
import SelectComponent from "../common/SelectComponent";
import Image from "next/image";
import Slider from "rc-slider";
export default function Sidebar() {
  const [price, setPrice] = useState([5000, 35000]);
  const handlePrice = (value) => {
    setPrice(value);
  };
  return (
    <div className="wrap-fixed-sidebar">
      <div className="sidebar-backdrop" />
      <div className="widget-sidebar-filter">
        <div className="fixed-sidebar-title">
          <h3>More Filter</h3>
          <a href="#" title="" className="close-filters">
            <Image
              alt=""
              src="/images/icons/close.svg"
              width={30}
              height={30}
            />
          </a>
        </div>
        <div className="inventory-sidebar">
          <div className="inventroy-widget widget-location">
            <div className="row">
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Location</label>

                  <SelectComponent
                    options={["New York", "Los Vegas", "California"]}
                  />
                </div>
              </div>
              <div className="col-lg-7">
                <div className="form_boxes">
                  <label>Search within</label>

                  <SelectComponent options={["200 miles", "", "200 miles"]} />
                </div>
              </div>
              <div className="col-lg-5">
                <div className="form_boxes">
                  <label>Zip Code</label>

                  <SelectComponent
                    options={["02111", "02111", "02111", "02111"]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Condition</label>

                  <SelectComponent
                    options={[
                      "New and Used",
                      "New York",
                      "Los Vegas",
                      "California",
                    ]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="categories-box">
                  <h6 className="title">Type</h6>
                  <div className="cheak-box">
                    <label className="contain">
                      SUV (1,456)
                      <input type="checkbox" defaultChecked="checked" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Sedan (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Hatchback (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Coupe (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Convertible (1,456)
                      <input type="checkbox" defaultChecked="checked" />
                      <span className="checkmark" />
                    </label>
                  </div>
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Make</label>

                  <SelectComponent
                    options={["New York", "Los Vegas", "California"]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Model</label>

                  <SelectComponent
                    options={[
                      "Add Model",
                      "New York",
                      "Los Vegas",
                      "California",
                    ]}
                  />
                </div>
              </div>
              <div className="col-lg-6">
                <div className="form_boxes">
                  <label>Min year</label>

                  <SelectComponent options={["2019", "2020", "2021", "2022"]} />
                </div>
              </div>
              <div className="col-lg-6">
                <div className="form_boxes">
                  <label>Max year</label>

                  <SelectComponent options={["2023", "2020", "2021", "2022"]} />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Mileage</label>

                  <SelectComponent
                    options={[
                      "Any Mileage",
                      "New York",
                      "Los Vegas",
                      "California",
                    ]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Drive Type</label>

                  <SelectComponent
                    options={[
                      "Any Type",
                      "New York",
                      "Los Vegas",
                      "California",
                    ]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="price-box">
                  <h6 className="title">Price</h6>
                  <form
                    onSubmit={(e) => e.preventDefault()}
                    className="row g-0"
                  >
                    <div className="form-column col-lg-6">
                      <div className="form_boxes">
                        <label>Min price</label>
                        <div className="drop-menu">${price[0]}</div>
                      </div>
                    </div>
                    <div className="form-column v2 col-lg-6">
                      <div className="form_boxes">
                        <label>Max price</label>
                        <div className="drop-menu">${price[1]}</div>
                      </div>
                    </div>
                  </form>
                  <div className="widget-price">
                    <Slider
                      formatLabel={() => ``}
                      range
                      max={50000}
                      min={0}
                      defaultValue={price}
                      onChange={(value) => handlePrice(value)}
                      id="slider"
                    />
                  </div>
                </div>
              </div>
              <div className="col-lg-12">
                <div className="categories-box border-none-bottom">
                  <h6 className="title">Transmission</h6>
                  <div className="cheak-box">
                    <label className="contain">
                      Automatic (1,456)
                      <input type="checkbox" defaultChecked="checked" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Manual (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      CVT (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                  </div>
                </div>
              </div>
              <div className="col-lg-12">
                <div className="categories-box border-none-bottom">
                  <h6 className="title">Fuel Type</h6>
                  <div className="cheak-box">
                    <label className="contain">
                      Diesel (1,456)
                      <input type="checkbox" defaultChecked="checked" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Petrol (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Hybird (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Electric (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                  </div>
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Exterior Color</label>

                  <SelectComponent
                    options={["Blue", "New York", "Los Vegas", "California"]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Interior Color</label>

                  <SelectComponent
                    options={["Black", "New York", "Los Vegas", "California"]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Doors</label>

                  <SelectComponent
                    options={["3", "New York", "Los Vegas", "California"]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="form_boxes">
                  <label>Cylinders</label>

                  <SelectComponent
                    options={["6", "New York", "Los Vegas", "California"]}
                  />
                </div>
              </div>
              <div className="col-lg-12">
                <div className="categories-box border-none-bottom m-0">
                  <h6 className="title">Key Features</h6>
                  <div className="cheak-box">
                    <label className="contain">
                      360-degree camera (1,456)
                      <input type="checkbox" defaultChecked="checked" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Bluetooth (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Keyless start (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Navigation System (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Active head restraints (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Brake assist (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                    <label className="contain">
                      Parking assist systems (1,456)
                      <input type="checkbox" />
                      <span className="checkmark" />
                    </label>
                  </div>
                  <a href="#" title="" className="show-more">
                    Show 8 more
                  </a>
                </div>
              </div>
            </div>
          </div>
          {/*widget end*/}
        </div>
      </div>
    </div>
  );
}
