/*
 * fbtest7.c
 *
 * http://raspberrycompote.blogspot.ie/2013/04/low-level-graphics-on-raspberry-pi-part.html
 *
 * Original work by J-P Rosti (a.k.a -rst- and 'Raspberry Compote')
 *
 * Licensed under the Creative Commons Attribution 3.0 Unported License
 * (http://creativecommons.org/licenses/by/3.0/deed.en_US)
 *
 * Distributed in the hope that this will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <math.h>
#include <fcntl.h>
#include <linux/input.h>
#include <linux/fb.h>
#include <sys/mman.h>

#define EVENT_DEVICE    "/dev/input/event0"
#define EVENT_TYPE      EV_ABS
#define EVENT_CODE_X    ABS_X
#define EVENT_CODE_Y    ABS_Y

// 'global' variables to store screen info
char *fbp = 0;
struct fb_var_screeninfo vinfo;
struct fb_fix_screeninfo finfo;

void put_pixel_RGB24(int x, int y, int r, int g, int b)
{
    // calculate the pixel's byte offset inside the buffer
    // note: x * 3 as every pixel is 3 consecutive bytes
    unsigned int pix_offset = x * 3 + y * finfo.line_length;

    // now this is about the same as 'fbp[pix_offset] = value'
    *((char*)(fbp + pix_offset)) = b;
    *((char*)(fbp + pix_offset + 1)) = g;
    *((char*)(fbp + pix_offset + 2)) = r;

}

void put_pixel_RGB565(int x, int y, int r, int g, int b)
{
    // calculate the pixel's byte offset inside the buffer
    // note: x * 2 as every pixel is 2 consecutive bytes
    unsigned int pix_offset = x * 2 + y * finfo.line_length;

    // now this is about the same as 'fbp[pix_offset] = value'
    // but a bit more complicated for RGB565
    //unsigned short c = ((r / 8) << 11) + ((g / 4) << 5) + (b / 8);
    unsigned short c = ((r / 8) * 2048) + ((g / 4) * 32) + (b / 8);
    // write 'two bytes at once'
    *((unsigned short*)(fbp + pix_offset)) = c;

}

// helper function for drawing - no more need to go mess with
// the main function when just want to change what to draw...
void draw() {

    int x, y;
    int r, g, b;
    int dr;
    int cr = vinfo.yres / 3;
    int cg = vinfo.yres / 3 + vinfo.yres / 4;
    int cb = vinfo.yres / 3 + vinfo.yres / 4 + vinfo.yres / 4;

    for (y = 0; y < (vinfo.yres); y++) {
        for (x = 0; x < vinfo.xres; x++) {
            r = 0;
            g = 0;
            b = 0;

            if (vinfo.bits_per_pixel == 16) {
                put_pixel_RGB565(x, y, r, g, b);
            }
            else {
                put_pixel_RGB24(x, y, r, g, b);
            }
        }
    }

    for (y = 0; y < (vinfo.yres) - 50; y++) {
        for (x = 0; x < vinfo.xres - 50; x++) {
            dr = (int)sqrt((cr - x)*(cr - x)+(cr - y)*(cr - y));
            r = 255 - 256 * dr / cr;
            r = (r >= 0) ? r : 0;
            dr = (int)sqrt((cg - x)*(cg - x)+(cr - y)*(cr - y));
            g = 255 - 256 * dr / cr;
            g = (g >= 0) ? g : 0;
            dr = (int)sqrt((cb - x)*(cb - x)+(cr - y)*(cr - y));
            b = 255 - 256 * dr / cr;
            b = (b >= 0) ? b : 0;

            if (vinfo.bits_per_pixel == 16) {
                put_pixel_RGB565(x + 50, y + 50, r, g, b);
            }
            else {
                put_pixel_RGB24(x + 50, y + 50, r, g, b);
            }
        }
    }

}

// application entry point
int main(int argc, char* argv[])
{

    int fbfd = 0;
    struct fb_var_screeninfo orig_vinfo;
    long int screensize = 0;

    struct input_event ev;
    int fd;
    char name[256] = "ADS7846 Touchscreen";


    // Open the file for reading and writing
    fbfd = open("/dev/fb1", O_RDWR);
    if (fbfd == -1) {
        printf("Error: cannot open framebuffer device.\n");
        return(1);
    }
    printf("The framebuffer device was opened successfully.\n");

    // Open Input Device
    fd = open(EVENT_DEVICE, O_RDONLY);
    if (fd == -1) {
        fprintf(stderr, "%s is not a vaild device\n", EVENT_DEVICE);
        return EXIT_FAILURE;
    }

    // Get variable screen information
    if (ioctl(fbfd, FBIOGET_VSCREENINFO, &vinfo)) {
        printf("Error reading variable information.\n");
    }
    printf("Original %dx%d, %dbpp\n", vinfo.xres, vinfo.yres, 
       vinfo.bits_per_pixel );

    // Store for reset (copy vinfo to vinfo_orig)
    memcpy(&orig_vinfo, &vinfo, sizeof(struct fb_var_screeninfo));

    // Get fixed screen information
    if (ioctl(fbfd, FBIOGET_FSCREENINFO, &finfo)) {
        printf("Error reading fixed information.\n");
    }

    // Print Input Device Name
    ioctl(fd, EVIOCGNAME(sizeof(name)), name);
    printf("Reading from:\n");
    printf("device file = %s\n", EVENT_DEVICE);
    printf("device name = %s\n", name);

    // map fb to user mem 
    screensize = finfo.smem_len;
    fbp = (char*)mmap(0, 
              screensize, 
              PROT_READ | PROT_WRITE, 
              MAP_SHARED, 
              fbfd, 
              0);

    if ((int)fbp == -1) {
        printf("Failed to mmap.\n");
    }
    else {
        // draw...
        draw();
        for (;;) {
            const size_t ev_size = sizeof(struct input_event);
            ssize_t size;

            /* TODO: use select() */

            size = read(fd, &ev, ev_size);
            if (size < ev_size) {
                fprintf(stderr, "Error size when reading\n");
                break;
            }

            if (ev.type == EVENT_TYPE && (ev.code == EVENT_CODE_X
                          || ev.code == EVENT_CODE_Y)) {
                /* TODO: convert value to pixels */
                printf("%s = %d\n", ev.code == EVENT_CODE_X ? "X" : "Y",
                        ev.value);

                // If touched reboot the system
                system("reboot");
            }
        }
    }

    // cleanup
    // unmap fb file from memory
    munmap(fbp, screensize);
    // reset the display mode
    if (ioctl(fbfd, FBIOPUT_VSCREENINFO, &orig_vinfo)) {
        printf("Error re-setting variable information.\n");
    }
    // close fb file    
    close(fbfd);

    return 0;
  
}
